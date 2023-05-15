<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\ProductCustomize;
use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\productFormat;
use App\Repository\Gestapp\ProductCustomizeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class ProductCustomizeController extends AbstractController
{
    /**
     * Ajouter une personnalisation sur le produit en cour
     * Envoie en Json - Réponse en Json
     * @Route("/gestapp/product/{id}/customize", name="op_gestapp_product_customize_new")
     */
    public function new(Request $request, Product $product, EntityManagerInterface $em)
    {
        // Récupération des données du formulaire ProductCustomize et intégration dans la table
        $datanew = json_decode($request->getContent(), true);
        //dd($datanew);
        $idformat = $datanew['format'];
        $sessid = $this->get('session')->getId();

        if(isset($datanew['name'])){
            $name = $datanew['name'];
        }else{
            $name = '';
        }

        $format = $em->getRepository(productFormat::class)->find($idformat);
        //dd($format);

        // Alimentation de la table
        $productCustomize = new ProductCustomize();

        $productCustomize->setName($name);
        $productCustomize->setFormat($format);
        $productCustomize->setUuid($sessid);
        $productCustomize->setProduct($product);
        //dd($productCustomize);
        $em->persist($productCustomize);
        $em->flush();

        return $this->json([
            'code' => 200,
            'message'=> "Les informations sur le produit ont été correctement ajouter.",
        ], 200);
    }

    /**
     * Editer une personnalisation sur le produit en cour
     * Envoie en Json - Réponse en Json
     * @Route("/gestapp/product/customize/{idCustom}/edit", name="op_gestapp_product_customize_edit")
     */
    public function edit($idCustom, ProductCustomizeRepository $productCustomizeRepository, Request $request, EntityManagerInterface $em)
    {
        $productCustomize = $productCustomizeRepository->find($idCustom);
        $data = json_decode($request->getContent(), true);

        $idformat = $data['format'];

        if(isset($data['name'])){
            $name = $data['name'];
        }else{
            $name = '';
        }

        $format = $em->getRepository(productFormat::class)->find($idformat);

        // Alimentation de l'objet ProductCustomize'
        $productCustomize->setFormat($format);
        $productCustomize->setName($name);

        $em->flush();

        return $this->redirectToRoute("op_webapp_cart_showcart");
    }
}