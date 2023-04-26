<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\PurchaseItem;
use App\Repository\Gestapp\PurchaseItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/gestapp/commande', name: 'app_gestapp_commande')]
    public function index(): Response
    {
        return $this->render('gestapp/commande/index.html.twig', [
            'controller_name' => 'CommandeController',
        ]);
    }

    #[Route('/gestapp/commande/duplicateitem/{item}', name: 'app_gestapp_commande_duplicateitem')]
    public function duplicateitem($item, PurchaseItemRepository $purchaseItemRepository): Response
    {
        $item = $purchaseItemRepository->find($item);

        $purchaseItem = clone ($item);
        $purchaseItem->setCustomerName("Inscrire un prénom");
        $purchaseItem->setProductQty(1);

        $price = intval($purchaseItem->getProductPrice());
        $qty = $purchaseItem->getProductQty();
        $purchaseItem->setTotalItem($qty*$price);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($purchaseItem);
        $entityManager->flush();

        dd($purchaseItem);

        return $this->render('gestapp/commande/index.html.twig', [
            'controller_name' => 'CommandeController',
        ]);
    }
}
