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

    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/gestapp/cart/{id}", name="op_webapp_cart_add", requirements={"id":"\d+"})
     */
    public function cart($id, Request $request, EntityManagerInterface $em): Response
    {
        // Récupération de l'objet produit
        $product = $this->productRepository->find($id);
        // teste si le produit existe dans la liste de produit.
        if(!$product){
            throw $this->createNotFoundException("Le produit portant l'identifiant $id n'existe pas.");
        }

        // Récupération des données du formulaire "ProductCustomize" et intégration dans la table
        $data = json_decode($request->getContent(), true);
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


        $cart = $this->cartService->getCart();
        if(count($cart) == 0){
            $item = 0;
            //dd($id);
            $productCustomize->setItem($item);
            $em->persist($productCustomize);
            $em->flush();
            $this->cartService->add($item, $product, $productCustomize);
        } else {
            $item = 0;
            $exist = 0;
            foreach ($cart as $c){
                //dd($c['Product']);
                if($c['ProductId'] == $id){
                    $exist = 1;
                    $item = $c['Item'];
                }
            }
            //dd($exist, $item);
            if($exist == 0){
                $item = array_key_last($cart)+1;
                $productCustomize->setItem($item);
                $em->persist($productCustomize);
                $em->flush();
                $this->cartService->add($item, $product, $productCustomize);
            }else{
                //dd('exit :'.$exist.', item :'. $item);
                $productCustomize->setItem($item);
                $em->persist($productCustomize);
                $em->flush();
                $this->cartService->increment($item, $product, $productCustomize);
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
     * @Route("/gestapp/cart/dulicate/{id}", name="op_webapp_cart_duplicate", requirements={"id":"\d+"})
     */
    public function duplicate($id, Request $request, EntityManagerInterface $em, ProductCustomizeRepository $productCustomizeRepository, CartRepository $cartRepository): Response
    {
        // Récupération de l'objet produit
        $product = $this->productRepository->find($id);

        // teste si le produit existe dans la liste de produit.
        if(!$product){
            throw $this->createNotFoundException("Le produit portant l'identifiant $id n'existe pas.");
        }

        $cart = $this->cartService->getCart();
        $item = array_key_last($cart)+1;
        $this->cartService->add($item, $id);

        /** Pour l'ajout de la livraison **/
        $form = $this->createForm(CartConfirmationType::class);

        //Récupération de l'id de session et des personnalisation
        $user = $this->getUser();
        $session = $this->get('session')->getId();

        $detailedCart = $this->cartService->getDetailedCartItem();

        //dd($detailedCart);

        foreach ($detailedCart as $d){
            // Construction des éléments nécessaire au panier
            $product = $d->product;
            $customization = $productCustomizeRepository->findLastProductCustomize($product->getId());
            $customidprod = $customization['uuid'];
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
                $cart->setCustomIdformat($customization['idformat']);
                $cart->setCustomFormat($customization['format']);
                $cart->setCustomName($customization['name']);
                $cart->setCustomPrice($customization['priceformat']);
                $cart->setCustomWeight($customization['weight']);
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
                    $cart->setCustomIdformat($customization['idformat']);
                    $cart->setCustomFormat($customization['format']);
                    $cart->setCustomName($customization['name']);
                    $cart->setCustomPrice($customization['priceformat']);
                    $cart->setCustomWeight($customization['weight']);
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

        return $this->render('gestapp/cart/index.html.twig', [
            'carts' => $cartspanel,
            'session' => $session,
            'user' => $user,
            'confirmationForm' => $form->createView()
        ]);
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

        //dd($session);

        foreach ($detailedCart as $d){
            //dd($d);
            // Construction des éléments nécessaire au panier
            $product = $d->product;
            $customization = $d->productCustomize;

            if($session == $customization->getUuid()){
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
                $cart->setUuid($customization->getUuid());
                $em->persist($cart);
                $em->flush();
            }
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
        $item = 0;
        foreach ($cart as $c){
            //dd($c['Product']);
            if($c['Product'] == $id){
                $item = $c['Item'];
            }
        }

        $this->cartService->decrement($item, $id);

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         

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
     * @Route("/webapp/cart/del/{id}/{item}", name="op_webapp_cart_delete", requirements={"id":"\d+"})
     */
    public function deleteProduct($id, $item, ProductRepository $productRepository, CartService $cartService, EntityManagerInterface $em, CartRepository $cartRepository)
    {
        $product = $productRepository->find($id);

        if(!$product){
            throw $this->createNotFoundException("Le produit $id n'existe pas et ne peut pas être supprimé !");
        }

        $listcustoms = $em->getRepository(ProductCustomize::class)->findBy(array('product'=>$id));
        foreach ($listcustoms as $custom){
            $em->remove($custom);
            $em->flush();
        }

        $carts = $cartRepository->findBy(['productId'=>$id]);
        foreach ($carts as $cart){
            $em->remove($cart);
            $em->flush();
        }

        $this->cartService->remove($item, $id);
        $this->addFlash("success", "le produit a bien été supprimé du panier");

        return $this->redirectToRoute("op_webapp_cart_showcart");

    }

}
