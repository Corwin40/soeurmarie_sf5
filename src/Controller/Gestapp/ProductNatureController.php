<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\ProductCategory;
use App\Entity\Gestapp\ProductNature;
use App\Form\Gestapp\ProductNature1Type;
use App\Form\Gestapp\ProductNatureType;
use App\Repository\Gestapp\ProductCategoryRepository;
use App\Repository\Gestapp\ProductNatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("")
 */
class ProductNatureController extends AbstractController
{
    /**
     * @Route("/opadmin/product/nature/", name="op_gestapp_product_nature_index", methods={"GET"})
     */
    public function index(ProductNatureRepository $productNatureRepository): Response
    {
        return $this->render('gestapp/product_nature/index.html.twig', [
            'product_natures' => $productNatureRepository->findAll(),
        ]);
    }

    /**
     * @Route("/opadmin/product/nature/new", name="op_gestapp_product_nature_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $productNature = new ProductNature();
        $form = $this->createForm(ProductNatureType::class, $productNature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($productNature);
            $entityManager->flush();

            return $this->redirectToRoute('op_gestapp_product_nature_index');
        }

        return $this->render('gestapp/product_nature/new.html.twig', [
            'product_nature' => $productNature,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/opadmin/product/nature/new2", name="op_gestapp_product_nature_new2", methods={"GET","POST"})
     */
    public function new2(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if(!$data)
        {
            $productNature = new ProductNature();
            $form = $this->createForm(ProductNatureType::class, $productNature);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($productNature);
                $entityManager->flush();

                return $this->redirectToRoute('op_gestapp_product_nature_index');
            }

            return $this->render('gestapp/product_category/new2.html.twig', [
                'product_category' => $productNature,
                'form' => $form->createView(),
            ]);
        }else{
            $name = $data['name'];
            $productNature = new ProductNature();
            $productNature->setName($name);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($productNature);
            $entityManager->flush();

            $nature = $productNature->getName();

            return $this->json([
                'code' => 200,
                'nature' => $nature,
                'message' => "Une nature de produit a été ajoutée."
            ], 200);
        }

    }

    /**
     * @Route("/opadmin/product/nature//{id}", name="op_gestapp_product_nature_show", methods={"GET"})
     */
    public function show(ProductNature $productNature): Response
    {
        return $this->render('gestapp/product_nature/show.html.twig', [
            'product_nature' => $productNature,
        ]);
    }

    /**
     * @Route("/opadmin/product/nature//{id}/edit", name="op_gestapp_product_nature_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ProductNature $productNature): Response
    {
        $form = $this->createForm(ProductNatureType::class, $productNature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gestapp_product_nature_index');
        }

        return $this->render('gestapp/product_nature/edit.html.twig', [
            'product_nature' => $productNature,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/opadmin/product/nature//{id}", name="op_gestapp_product_nature_delete", methods={"POST"})
     */
    public function delete(Request $request, ProductNature $productNature): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productNature->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($productNature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('gestapp_product_nature_index');
    }

    /**
     * Suppression d'une ligne index.php
     * @Route("/gestapp/product/nature/delevent/{id}", name="op_gestapp_product_nature_del", methods={"POST"})
     */
    public function DelEvent(Request $request, ProductNature $productNature, ProductCategory $productCategory) : Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $productNatures = $productNature->getProductCategories()->getValues();
        $products = $productNature->getProduct()->getValues();
        //dd($products);
        if($productNatures){
            foreach ( $productNatures as $productCategory) {
                $productNature->removeProductCategory($productCategory);
                $entityManager->flush();
            }
        }
        if($products){
            foreach ( $products as $product) {
                $productNature->removeProduct($product);
                $entityManager->flush();
            }
        }

        $entityManager->remove($productNature);
        $entityManager->flush();

        $productnatures = $this->getDoctrine()->getRepository(ProductNature::class)->findAll();

        return $this->json([
            'code'=> 200,
            'message' => "La nature a été supprimé",
            'liste' => $this->renderView('gestapp/product_nature/include/_liste.html.twig', [
                'product_natures' => $productnatures
            ])
        ], 200);
    }

    /**
     * @Route("/opadmin/product/listnature", name="op_gestapp_product_category_list", methods={"GET"})
     */
    public function listNatures(ProductNatureRepository $productNatureRepository, ProductCategoryRepository $productCategoryRepository)
    {
        $natures = $productNatureRepository->findAll();

        $categories = $productCategoryRepository->CatByNat();

        return $this->render('gestapp/product_nature/listnatures.html.twig',[
            'natures' => $natures,
            'categories' => $categories
        ]);
    }

    /**
     * Liste les categories selon la nature pour les "select"
     * @Route("/opadmin/product/selectcategories/{id}", name="op_gestapp_product_category_selectcategories", methods={"GET"})
     */
    public function SelectCategories(ProductNature $nature, ProductCategoryRepository $productCategoryRepository, )
    {
        $categories = $productCategoryRepository->SelectCategories($nature->getId());
        return $this->json([
            'code' => 200,
            'categories' => $categories,
        ], 200);
    }
}
