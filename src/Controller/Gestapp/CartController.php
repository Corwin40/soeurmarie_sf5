<?php

namespace App\Controller\Gestapp;

use App\Cart\CartService;
use App\Entity\Gestapp\Cart;
use App\Entity\Gestapp\ProductCustomize;
use App\Entity\Gestapp\productFormat;
use App\Form\Gestapp\CartConfirmationType;
use App\Repository\Gestapp\CartRepository;
use App\Repository\Gestapp\ProductCustomizeRepository;
use App\Repository\Gestapp\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    protected $productRepository;
    protected $cartService;
    private $customizeRepository;

    public function __construct(ProductRepository $productRepository, CartService $cartService, ProductCustomizeRepository $customizeRepository)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
        $this->customizeRepository = $customizeRepository;
    }

    /**
     * @Route("/gestapp/cart/{id}", name="op_webapp_cart_add", requirements={"id":"\d+"})
     */
    public function cart($id, Request $request, EntityManagerInterface $em): Response
    {
        $parametres = $request->query->all();

        // Récupération de l'objet produit
        $product = $this->productRepository->find($id);
        // teste si le produit existe dans la liste de produit.
        if(!$product){
            throw $this->createNotFoundException("Le produit portant l'identifiant $id n'existe pas.");
        }

        // Récupération des données du formulaire "ProductCustomize" et intégration dans la table
        $data = json_decode($request->getContent(), true);
        // On teste la présence du formulaire de personnalisation
        if($data){
            $idformat = $data['format'];
            $sessid = $this->get('session')->getId();

            if(isset($data['name'])){
                $name = $data['name'];
            }else{
                $name = '';
            }

            $format = $em->getRepository(productFormat::class)->find($idformat);

            // initialisation de la table ProductCustomize
            $productCustomize = new ProductCustomize();

            $productCustomize->setName($name);
            $productCustomize->setFormat($format);
            $productCustomize->setUuid($sessid);
            $productCustomize->setProduct($product);

            $CustomizeName = $productCustomize->getName();
            $CustomizeFormat = $productCustomize->getFormat()->getId();
            $CustomizeUuid = $productCustomize->getUuid();
        }

        $cart = $this->cartService->getCart();
        // CONDITION :
        // Si le panier est vide : item à 0 et on ajoute la personnalisation
        // Sinon on boucle sur le panier pour identifier si un produit existe parmi les items.
        if(count($cart) == 0){
            $item = 0;
            $productCustomize->setItem($item);
            $em->persist($productCustomize);
            $em->flush();
            $this->cartService->add($item, $product, $productCustomize);
        }
        else {
            $item = 0;
            $exist = 0;
            foreach ($cart as $c){
                if($c['ProductId'] == $id){
                    $exist = 1;
                    $item = $c['Item'];
                }
            }
            // CONDITION :
            // Si le produit est absent : on l'ajoute au panier avec sa personnalisation.
            // Si le prdoduit existe : on récupère sa personnalisation et on incrémente la quantité
            if($exist == 0){
                $item = array_key_last($cart)+1;
                $productCustomize->setItem($item);
                $em->persist($productCustomize);
                $em->flush();
                $this->cartService->add($item, $product, $productCustomize);
            }else{
                if($request->query->has('item')){

                    // Récupération de la personnalisation
                    $uuid = $this->get('session')->getId();
                    $parametres = $request->query->all();
                    $Customize = $this->customizeRepository->findCart($id, $uuid, intval($parametres['item']));
                    $CustomizeObject = $this->customizeRepository->find($Customize['id']);
                    $this->cartService->increment(intval($parametres['item']), $product, $CustomizeObject);
                }else{
                    // Récupération de la personnalisation
                    $uuid = $this->get('session')->getId();
                    $Customize = $this->customizeRepository->findCart($id, $uuid, $item);
                    $CustomizeObject = $this->customizeRepository->find($Customize['id']);
                    //dd($CustomizeObject);
                    $this->cartService->increment($item, $product, $CustomizeObject);
                }

            }

        }
        //dd($cart);
        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        if($request->query->get('returnToCart')){
            return $this->redirectToRoute('op_webapp_cart_showcartjson');
        }elseif($request->query->get('showproduct')){
            return $this->redirectToRoute('op_gestapp_cart_showcartcount',[
                'id' => $id
            ]);
        }else{
            return $this->redirectToRoute('op_gestapp_product_show', [
                'id' => $id
            ]);
        }
    }

    /**
     * @Route("/gestapp/cart/duplicate/{id}/{uuid}/{item}", name="op_webapp_cart_duplicate", requirements={"id":"\d+"})
     */
    public function duplicate($id, $uuid, $item, Request $request, EntityManagerInterface $em, ProductCustomizeRepository $productCustomizeRepository, CartRepository $cartRepository): Response
    {
        $product = $this->productRepository->find($id);
        // 1. Création dun nouvel item pour le panier
        $cart = $this->cartService->getCart();
        $newitem = array_key_last($cart)+1;
        $Customize = $this->customizeRepository->findCart($id, $uuid, $item);
        $CustomizeObject = $this->customizeRepository->find($Customize['id']);
        // 2. Duplication de la personnalisation
        $newCustomizeObject = Clone($CustomizeObject);
        $newCustomizeObject->setName("");
        $newCustomizeObject->setItem($newitem);
        $em->persist($newCustomizeObject);
        $em->flush();

        // 4. On ajoute le produit dupliqué directement dans le panier
        $this->cartService->add($newitem, $product, $CustomizeObject);

        return $this->redirectToRoute('op_webapp_cart_showcartjson');
    }

    /**
     * Liste les produits inclus dans le panier
     * @Route("/webapp/cart/show", name="op_webapp_cart_showcart")
     */
    public function showCart(Request $request, EntityManagerInterface $em, ProductCustomizeRepository $productCustomizeRepository, CartRepository $cartRepository)
    {
        $user = $this->getUser();
        /** Pour l'ajout de la livraison **/
        $form = $this->createForm(CartConfirmationType::class);

        //Récupération de l'id de session et des personnalisation
        $session = $this->get('session')->getId();

        $detailedCart = $this->cartService->getDetailedCartItem();

        //dd($detailedCart);

        foreach ($detailedCart as $d){
            //dd($d);
            // Construction des éléments nécessaire au panier
            $product = $d->product;
            $customization = $d->productCustomize;

            //dd($session, $customization->getUuid());

            if($session != $customization->getUuid()){
                $customization->setUuid($session);
                $em->persist($customization);
                $em->flush();
                $this->cartService->updateUuid($d->item, $customization);
            }

            //dd($session, $customization, $this->cartService->getCart());

            $cart = new Cart();
            $cart->setProductId($product->getId());
            $cart->setProduct($product);
            $cart->setProductName($product->getName());
            $cart->setProductNature($product->getProductNature());
            $cart->setproductCategory($product->getProductCategory());
            $cart->setProductQty($d->qty);
            $cart->setProductRef($product->getRef());
            $cart->setCustomId($customization->getId());
            $cart->setCustomIdformat($customization->getFormat()->getId());
            $cart->setCustomFormat($customization->getFormat()->getName());
            $cart->setCustomName($customization->getName());
            $cart->setCustomPrice($customization->getFormat()->getPriceformat());
            $cart->setCustomWeight($customization->getFormat()->getWeight());
            $cart->setItem($d->item);
            $cart->setUuid($customization->getUuid());
            $em->persist($cart);
            $em->flush();
        }
        $carts = $cartRepository->findBy(['uuid'=> $session]);
        $cartspanel = $carts;
        foreach($carts as $cart){
            $em->remove($cart);
            $em->flush();
        }

        //dd($cartspanel);

        return $this->render('gestapp/cart/index.html.twig', [
            'carts' => $cartspanel,
            'session' => $session,
            'user' => $user,
            'confirmationForm' => $form->createView()
        ]);
    }

    /**
     * Liste les produits inclus dans le panier
     * @Route("/webapp/cart/showjson", name="op_webapp_cart_showcartjson")
     */
    public function showCartJson(Request $request, EntityManagerInterface $em, ProductCustomizeRepository $productCustomizeRepository, CartRepository $cartRepository)
    {
        $form = $this->createForm(CartConfirmationType::class);
        $user = $this->getUser();

        //Récupération de l'id de session et des personnalisation
        $session = $this->get('session')->getId();

        $detailedCart = $this->cartService->getDetailedCartItem();

        foreach ($detailedCart as $d){
            $product = $d->product;
            $customization = $productCustomizeRepository->findOneBy(['product'=> $d->product]);
            //dd($customization);
            $customidprod = $customization->getUuid();

            $uuid = $cartRepository->findOneBy(['uuid'=> $session], ['id'=> 'DESC']);

            if(!$uuid){
                $cart = new Cart();
                $cart->setProductId($product->getId());
                $cart->setProduct($product);
                $cart->setProductName($product->getName());
                $cart->setProductNature($product->getProductNature());
                $cart->setproductCategory($product->getProductCategory());
                $cart->setProductQty($d->qty);
                $cart->setProductRef($product->getRef());
                $cart->setCustomIdformat($customization->getFormat()->getId());
                $cart->setCustomFormat($customization->getFormat()->getName());
                $cart->setCustomName($customization->getName());
                $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                $cart->setCustomWeight($customization->getFormat()->getWeight());
                $cart->setItem($d->item);
                $cart->setUuid($session);
                $em->persist($cart);
                $em->flush();
            }else{
                $cartproduct = $uuid->getProductId();
                if($customidprod != $cartproduct)
                {
                    $cart = new Cart();
                    $cart->setProductId($product->getId());
                    $cart->setProduct($product);
                    $cart->setProductName($product->getName());
                    $cart->setProductNature($product->getProductNature());
                    $cart->setproductCategory($product->getProductCategory());
                    $cart->setProductQty($d->qty);
                    $cart->setProductRef($product->getRef());
                    $cart->setCustomIdformat($customization->getFormat()->getId());
                    $cart->setCustomFormat($customization->getFormat()->getName());
                    $cart->setCustomName($customization->getName());
                    $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                    $cart->setCustomWeight($customization->getFormat()->getWeight());
                    $cart->setItem($d->item);
                    $cart->setUuid($session);
                    $em->persist($cart);
                    $em->flush();
                }
            }
        }
        $carts = $cartRepository->findBy(['uuid'=> $session]);
        $cartspanel = $carts;
        foreach($carts as $cart){
            $em->remove($cart);
            $em->flush();
        }

        //dd($cartspanel);

        // Retourne une réponse en json
        return $this->json([
            'code'          => 200,
            'message'       => "Le produit a été correctement supprimé.",
            'liste'         => $this->renderView('gestapp/cart/include/_liste.html.twig', [
                'carts' => $cartspanel,
                'session' => $session,
                'user' => $user,
                'confirmationForm' => $form->createView()
            ])
        ], 200);
    }

    /**
     * Liste les produits inclus dans le panier
     * @Route("/webapp/cart/showcartcount/{id}", name="op_gestapp_cart_showcartcount")
     */
    public function showcartcount($id, Request $request, EntityManagerInterface $em, CartService $cartService)
    {
        $detailedCart = $this->cartService->getDetailedCartItem();
        $product = $this->productRepository->find($id);
        $session = $request->getSession()->get('name_uuid');
        $productCustomize = $em->getRepository(ProductCustomize::class)->findOneBy(array('product' => $product->getId()), array('id'=>'DESC'));

        // Retourne une réponse en json
        return $this->json([
            'code'          => 200,
            'message'       => "Le produit a été correctement ajouté.",
            'count'         => $this->renderView('gestapp/product/include/_count.html.twig', [
                'items' => $detailedCart,
                'product' => $product,
                'session' => $session,
                'customizes' => $productCustomize
            ]),
            'lipanier' => $this->renderView('include/_panier.html.twig', ['cartService'=> $cartService])
        ], 200);
    }

    /**
     * @Route("/gestapp/cart/decrement/{id}", name="op_webapp_cart_decrement", requirements={"id":"\d+"})
     */
    public function decrementeCart($id, Request $request): Response
    {
        $product = $this->productRepository->find($id);

        // teste si le produit existe dans la liste de produit.
        if(!$product){
            throw $this->createNotFoundException("Le produit portant l'identifiant $id n'existe pas et ne peut être diminué dans le panier.");
        }

        $cart = $this->cartService->getCart();

        if($request->query->has('item')){
            $parametres = $request->query->all();
            $this->cartService->decrement(intval($parametres['item']), $id);
        }else{
            $item = 0;
            foreach ($cart as $c){
                //dd($c['Product']);
                if($c['ProductId'] == $id){
                    $item = $c['Item'];
                }
            }
            $this->cartService->decrement($item, $id);
        }

        $this->addFlash('success', "Le produit a bien été diminué dans le panier.");

        if($request->query->get('returnToCart')){
            return $this->redirectToRoute('op_webapp_cart_showcartjson');
        }
        elseif ($request->query->get('showproduct')){
            return $this->redirectToRoute('op_gestapp_cart_showcartcount',[
                'id' => $id
            ]);
        }

        return $this->redirectToRoute('op_gestapp_product_show', [
            'id' => $id
        ]);
    }

    /**
     * Supprime un produit du panier
     * @Route("/webapp/cart/del/{id}/{uuid}/{item}", name="op_webapp_cart_delete", requirements={"id":"\d+"})
     */
    public function deleteProduct($id, $item, $uuid, ProductRepository $productRepository, CartService $cartService, EntityManagerInterface $em)
    {

        $Customize = $this->customizeRepository->findCart($id, $uuid, $item);
        $CustomizeObject = $this->customizeRepository->find($Customize['id']);

        $em->remove($CustomizeObject);
        $em->flush();

        $this->cartService->remove($item, $id, $uuid);

        return $this->redirectToRoute("op_webapp_cart_showcart");

    }

}
