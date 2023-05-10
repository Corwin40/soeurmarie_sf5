<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\ProductCustomize;
use App\Entity\Gestapp\productFormat;
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
     * @Route("/gestapp/product/customize/edit/{id}", name="op_gestapp_product_customize_edit")
     */
    public function edit(Request $request, Product $product, EntityManagerInterface $em)
    {
        // récupération des données du formaulaire ProductCustomize et intégration dans la table
        $data = json_decode($request->getContent(), true);
        //dd($data);
        $idformat = $data['format'];
        $sessid = $this->get('session')->getId();

        if(isset($data['name'])){
            $name = $data['name'];
        }else{
            $name = '';
        }

        $format = $em->getRepository(productFormat::class)->find($idformat);

        // Alimentation de la table
        $productCustomize = $em->getRepository(ProductCustomize::class)->findOneBy(array('product'=>$product->getId()), array('id'=>'DESC'));

        $productCustomize->setFormat($format);
        $productCustomize->setUuid($sessid);
        $productCustomize->setProduct($product);
        $productCustomize->setName($name);

        $em->flush();

        return $this->json([
            'code' => 200,
            'message'=> "Les informations sur le produit ont été correctement modifiées.",
        ], 200);
    }
}