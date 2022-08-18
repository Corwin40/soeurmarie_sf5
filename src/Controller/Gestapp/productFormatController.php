<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\productFormat;
use App\Form\Gestapp\productFormatType;
use App\Repository\Gestapp\productFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gestapp/product/format')]
class productFormatController extends AbstractController
{
    #[Route('/', name: 'op_gestapp_product_format_index', methods: ['GET'])]
    public function index(productFormatRepository $productFormatRepository): Response
    {
        return $this->render('gestapp/product_format/index.html.twig', [
            'product_formats' => $productFormatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'op_gestapp_product_format_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productFormat = new productFormat();
        $form = $this->createForm(productFormatType::class, $productFormat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productFormat);
            $entityManager->flush();

            return $this->redirectToRoute('op_gestapp_product_format_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gestapp/product_format/new.html.twig', [
            'product_format' => $productFormat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'op_gestapp_product_format_show', methods: ['GET'])]
    public function show(productFormat $productFormat): Response
    {
        return $this->render('gestapp/product_format/show.html.twig', [
            'product_format' => $productFormat,
        ]);
    }

    #[Route('/{id}/edit', name: 'op_gestapp_product_format_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, productFormat $productFormat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(productFormatType::class, $productFormat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('op_gestapp_product_format_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gestapp/product_format/edit.html.twig', [
            'product_format' => $productFormat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'op_gestapp_product_format_delete', methods: ['POST'])]
    public function delete(Request $request, productFormat $productFormat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productFormat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($productFormat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('op_gestapp_product_format_index', [], Response::HTTP_SEE_OTHER);
    }
}
