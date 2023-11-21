<?php

namespace App\Controller\Gestapp\Purchase;

use App\Cart\CartService;
use App\Entity\Gestapp\ProductCustomize;
use App\Entity\Gestapp\Purchase;
use App\Entity\Gestapp\PurchaseItem;
use App\Form\Gestapp\CartConfirmationType;
use App\Repository\Gestapp\PurchaseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseConfirmationController extends AbstractController
{

    protected $cartService;
    protected $em;
    private $requestStack;


    public function __construct(CartService $cartService, EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->cartService = $cartService;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/webapp/purchase/confirm", name="op_webapp_purchase_confirm")
     * @IsGranted("ROLE_USER", message="Vous devez être inscrit sur la plateforme pour confirmer votre commande")
     */
    public function confirm(Request $request, EntityManagerInterface $em, PurchaseRepository $purchaseRepository)
    {
        $form = $this->createForm(CartConfirmationType::class);
        $form->handleRequest($request);

        if(!$form->isSubmitted()) {
            $this->addFlash('warning', 'vous devez compléter le formulaire');
            return $this->redirectToRoute('op_webapp_cart_showcart');
        }

        $user = $this->getUser();
        $lastPurchase = $purchaseRepository->findLastRef();
        //dd($lastPurchase);
        $lastRef = $lastPurchase->getNumPurchase();

        $cartItems = $this->cartService->getDetailedCartItem();
        if(count($cartItems) === 0){
            $this->addFlash('warning', 'le panier est vide, impossible de commander');
            return $this->redirectToRoute('op_webapp_cart_showcart');
        }

        /** @var Purchase */
        $purchase = $form->getData();
        //dd($this->cartService->getTotal());
        // contruction du numero de commande
        $date = new \DateTime();
        $numDate = $date->format('Y').'|'.$date->format('m');
        $ref = $numDate."-".$lastRef;

        $purchase
            ->setCustomer($user)
            ->setNumPurchase($ref)
            ->setStatus("PENDING")
            ->setStatuspaid("PENDING")
            ->setPurchasedAt(new DateTime())
            ->setTotal($this->cartService->getTotal());

        $this->em->persist($purchase);
        $total = 0;

        foreach($this->cartService->getDetailedCartItem() as $cartItem){
            //récupération des personnalisation du produit
            $product = $cartItem->product;
            $listCustom = $em->getRepository(ProductCustomize::class)->findOneBy(array('product'=> $product), array('id'=>'DESC'));
            $format = $listCustom->getFormat();
            $priceformat = $listCustom->getFormat()->getPriceformat();

            $purchaseItem = new PurchaseItem;
            $purchaseItem
                ->setPurchase($purchase)
                ->setProduct($cartItem->product)
                ->setProductName($cartItem->product->getName())
                ->setProductQty($cartItem->qty)
                ->setProductPrice($priceformat)
                ->setTotalItem($cartItem->qty*$priceformat)
                ->setFormat($format)
                ->setCustomerName($listCustom->getName())
            ;
            $this->em->persist($purchaseItem);
            $em->remove($listCustom);

            $total = $total + $purchaseItem->getTotalItem();
        }

        $this->em->flush();
        $this->cartService->emptyCart();
        $this->addFlash('success', "La commande est enregistré");

        $purchase->setTotal($total);
        $this->em->flush();

        // Renouvellement de la session
        $session = $this->get('session');
        $session->migrate();

        return $this->redirectToRoute('op_webapp_purchases_index');
    }

    /**
     * Supprime la commande sélectionnée en amont et rafraichi la page de l'utilisateur courant
     * @param Purchase $purchase
     * @Route("/gestapp/purchase/delete/{id}", name="op_gestapp_purchase_delete")
     */
    public function deletePurchase(Purchase $purchase)
    {
        $this->em->remove($purchase);
        $this->em->flush();

        $user = $this->getUser();
        $purchases =  $this->em->getRepository(Purchase::class)->findBy(array('customer'=>$user));

        return $this->json([
            'code'          => 200,
            'message'       => 'La commande et son contenu à été effacée.',
            'liste'         =>  $this->renderView('gestapp/purchase/include/_liste.html.twig', [
                'purchases' => $purchases,
                'hide' => 0
            ])
        ], 200);
    }
}