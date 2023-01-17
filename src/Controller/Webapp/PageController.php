<?php

namespace App\Controller\Webapp;

use App\Entity\Webapp\Article;
use App\Entity\Webapp\Page;
use App\Entity\Webapp\Section;
use App\Form\Webapp\PageType;
use App\Repository\Webapp\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/webapp/page/", name="op_webapp_page_index", methods={"GET"})
     */
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('webapp/page/index.html.twig', [
            'pages' => $pageRepository->sortPosition(),
        ]);
    }

    /**
     * @Route("/webapp/page/new/", name="op_webapp_page_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        // OPn déclare les variables utiles à la méthode
        $member = $this->getUser();

        // on alimente une nouvelle page -> l'objet
        $page = new Page();
        $page->setAuthor($member);
        $page->setPosition(1);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on persiste en base de donée le nouvel objet
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            // on alimente une nouvelle section au nouvel objet fraichement créer et on le persiste
            $section = new Section();
            $section->setTitle('nouvelle section');
            $section->setDescription('');
            $section->setContentType('none');
            $section->setPosition(1);
            $section->setPage($page);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_page_index');
        }

        return $this->render('webapp/page/new.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/page/new/{position}", name="op_webapp_page_newposition", methods={"GET","POST"})
     */
    public function newPosition(Request $request, $position): Response
    {
        // OPn déclare les variablezs utiles à la méthode
        $member = $this->getUser();
        $newpos = $position +1;

        // on alimente une nouvelle page -> l'objet
        $page = new Page();
        $page->setAuthor($member);
        $page->setPosition($newpos);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on persiste en base de donée le nouvel objet
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            // on alimente une nouvelle section au nouvel objet fraichement créer et on le persiste
            $section = new Section();
            $section->setTitle('nouvelle section');
            $section->setDescription('');
            $section->setContentType('none');
            $section->setPosition(1);
            $section->setPage($page);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_page_index');
        }

        return $this->render('webapp/page/new.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * affiche la page en front office selon le slug
     * @Route("/webapp/page/{slug}", name="op_webapp_page_slug", methods={"GET"})
     */
    public function page($slug) : response
    {
        $page = $this->getDoctrine()->getRepository(Page::class)->findbyslug($slug);

        return $this->render('webapp/page/page.html.twig');
    }

    /**
     * @Route("/webapp/page/{id}", name="op_webapp_page_show", methods={"GET"})
     */
    public function show(Page $page): Response
    {
        return $this->render('webapp/page/show.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/webapp/page/{id}/edit", name="op_webapp_page_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('op_webapp_page_index');
        }

        return $this->render('webapp/page/edit.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/page/{id}", name="op_webapp_page_delete", methods={"POST"})
     */
    public function delete(Request $request, Page $page): Response
    {
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($page);
            $entityManager->flush();
        }

        return $this->redirectToRoute('op_webapp_page_index');
    }

    /**
     * Suppression d'une ligne index.php
     * @Route("/webapp/page/del/{id}", name="op_Gestapp_recommandation_del", methods={"POST"})
     */
    public function DelEvent(Request $request, Page $page, Article $article, EntityManagerInterface $entityManager) : Response
    {
       $arts = $page->getLinkedPage()->getValues();
        foreach ($arts as $article){
            $page->removeLinkedPage($article);
        }

        $entityManager->remove($page);
        $entityManager->flush();

        $pages = $entityManager->getRepository(Page::class)->sortPosition();

        return $this->json([
            'code'=> 200,
            'message' => "L'évènenemt a été supprimé",
            'liste' => $this->renderView('webapp/page/include/_liste.html.twig', [
                'pages' => $pages
            ])
        ], 200);
    }

    /**
     * Affiche une page du site par son slug
     *
     * @param PageRepository $pageRepository
     * @param $slug
     * @return Response
     * @Route("/page/{slug}", name="op_webapp_page_slug")
     */
    public function pagebyslug(PageRepository $pageRepository, $slug): Response
    {
        $page = $pageRepository->findOneBy(array('slug' => $slug));

        return $this->render('webapp/page/page.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * Permet d'activer ou de désactiver la publication
     * @Route("/webapp/page/publish/{id}/json", name="op_webapp_page_publish")
     */
    public function jspublish(Page $page, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $ispublish = $page->getIsPublish();
        // renvoie une erreur car l'utilisateur n'est pas connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // Si la page est déja publiée, alors on dépublie
        if($ispublish == true){
            $page->setIsPublish(0);
            $em->flush();
            return $this->json([
                'code'      => 200,
                'message'   => 'La page est dépubliée'
            ], 200);
        }
        // Si la page est déja dépubliée, alors on publie
        $page->setIsPublish(1);
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => 'La page est publiée'
        ], 200);
    }

    /**
     * Permet de mettre en menu la poge ou non
     * @Route("/webapp/page/menu/{id}/json", name="op_webapp_page_menu")
     */
    public function jsMenu(Page $page, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $ismenu = $page->getIsMenu();
        // renvoie une erreur car l'utilisateur n'est pas connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // Si la page est déja publiée, alors on dépublie
        if($ismenu == true){
            $page->setIsMenu(0);
            $em->flush();
            return $this->json(['code'=> 200, 'message' => 'La page est dépublié des menus'], 200);
        }
        // Si la page est déja dépubliée, alors on publie
        $page->setIsMenu(1);
        $em->flush();
        return $this->json([
            'code'=> 200,
            'message' => 'La page est publié dans les menus'
        ], 200);
    }

    /**
     * Permet de down la position de la page
     * @Route("/webapp/page/position/{id}/{level}", name="op_webapp_page_position_down")
     */
    public function Position(Page $page, $level, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $id = $page->getId();

        // On récupére la position de la page actuelle et on prépare des les futures positions +1 et -1.
        $position = $page->getPosition();
        $nextPos = $page->getPosition()+1;
        $previousPos = $page->getPosition()-1;

        if($level == 'up')
        {
            $previousItem = $em->getRepository(Page::class)->findOneBy(array('position' => $previousPos));
            $page->setPosition($previousPos);
            $previousItem->setPosition($position);
            $em->flush();
            // on récupère la liste des pages ordonnée par position
            $pages = $this->getDoctrine()->getRepository(Page::class)->sortPosition();
            return $this->json([
                'code'=> 200,
                'message' => "La page est montée d'un niveau",
                'liste' => $this->renderView('webapp/page/include/_liste.html.twig', [
                    'pages' => $pages
                ])
            ], 200);
        }elseif($level == 'down'){
            $nextItem = $em->getRepository(Page::class)->findOneBy(array('position' => $nextPos));
            $page->setPosition($nextPos);
            $nextItem->setPosition($position);
            $em->flush();
            // on récupère la liste des pages ordonnée par position
            $pages = $this->getDoctrine()->getRepository(Page::class)->sortPosition();
            return $this->json([
                'code'=> 200,
                'message' => "La page est descendu d'un niveau",
                'liste' => $this->renderView('webapp/page/include/_liste.html.twig', [
                    'pages' => $pages
                ])
            ], 200);
        };
    }


}
