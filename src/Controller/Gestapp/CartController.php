<?php

namespace App\Controller\Gestapp;

use App\Cart\CartService;
use App\Entity\Gestapp\Cart;
use App\Entity\Gestapp\ProductCustomize;
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
    public function cart($id, Request $request): Response
    {
        // Récupération de l'objet produit
        $product = $this->productRepository->find($id);

        // teste si le produit existe dans la liste de produit.
        if(!$product){
            throw $this->createNotFoundException("Le produit portant l'identifiant $id n'existe pas.");
        }

        $this->cartService->increment($id);

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
            $product = $d->product;
            //dd($product);
            $customization = $productCustomizeRepository->findOneBy(['product'=> $d->product]);
            $customidprod = $customization->getUuid();
            //dd($customization->getUuid());
            $uuid = $cartRepository->findOneBy(['uuid'=> $session], ['id'=> 'DESC']);
            //dd($uuid);


            if(!$uuid){
                $cart = new Cart();
                $cart->setProductId($product->getId());
                $cart->setProduct($product);
                $cart->setProductName($product->getName());
                $cart->setProductNature($product->getProductNature());
                $cart->setproductCategory($product->getProductCategory());
                $cart->setProductQty($d->qty);
                $cart->setProductRef($product->getRef());
                $cart->setCustomFormat($customization->getFormat()->getName());
                $cart->setCustomName($customization->getName());
                $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                $cart->setCustomWeight($customization->getFormat()->getWeight());
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
                    $cart->setCustomFormat($customization->getFormat()->getName());
                    $cart->setCustomName($customization->getName());
                    $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                    $cart->setCustomWeight($customization->getFormat()->getWeight());
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
            //dd($product);
            $customization = $productCustomizeRepository->findOneBy(['product'=> $d->product]);
            $customidprod = $customization->getUuid();
            //dd($customization->getUuid());
            $uuid = $cartRepository->findOneBy(['uuid'=> $session], ['id'=> 'DESC']);
            //dd($uuid);


            if(!$uuid){
                $cart = new Cart();
                $cart->setProductId($product->getId());
                $cart->setProduct($product);
                $cart->setProductName($product->getName());
                $cart->setProductNature($product->getProductNature());
                $cart->setproductCategory($product->getProductCategory());
                $cart->setProductQty($d->qty);
                $cart->setProductRef($product->getRef());
                $cart->setCustomFormat($customization->getFormat()->getName());
                $cart->setCustomName($customization->getName());
                $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                $cart->setCustomWeight($customization->getFormat()->getWeight());
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
                    $cart->setCustomFormat($customization->getFormat()->getName());
                    $cart->setCustomName($customization->getName());
                    $cart->setCustomPrice($customization->getFormat()->getPriceformat());
                    $cart->setCustomWeight($customization->getFormat()->getWeight());
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

        $this->cartService->decrement($id);

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         

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
     * @Route("/webapp/cart/del/{id}", name="op_webapp_cart_delete", requirements={"id":"\d+"})
     */
    public function deleteProduct($id, ProductRepository $productRepository, CartService $cartService, EntityManagerInterface $em, CartRepository $cartRepository)
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

        $this->cartService->remove($id);
        $this->addFlash("success", "le produit a bien été supprimé du panier");

        return $this->redirectToRoute("op_webapp_cart_showcart");

    }

}
