<?php

namespace App\Controller\Webapp;

use App\Entity\Webapp\Page;
use App\Entity\Webapp\Section;
use App\Form\Webapp\SectionType;
use App\Form\Webapp\Section2Type;
use App\Repository\Webapp\SectionRepository;
use CKSource\CKFinder\Response\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SectionController extends AbstractController
{
    /**
     * @Route("/webapp/section/", name="op_webapp_section_index", methods={"GET"})
     */
    public function index(SectionRepository $sectionRepository): Response
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findWithPage();

        return $this->render('webapp/section/index.html.twig', [
            'sections' => $sections,
        ]);
    }

    /**
     * @Route("/webapp/section/bypage/{page}", name="op_webapp_section_bypage", methods={"GET"})
     */
    public function byPage(SectionRepository $sectionRepository, $page): Response
    {
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);

        return $this->render('webapp/section/bypage.html.twig', [
            'sections' => $sectionRepository->findbypage($page),
            'element' => $element,
        ]);
    }

    /**
     * @Route("/webapp/section/frontbypage/{id}", name="op_webapp_section_frontbypage", methods={"GET"})
     */
    public function frontByPage(Page $page): Response
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('page' => $page), array('position' => 'ASC'));
        return $this->render('webapp/section/frontbypage.html.twig', [
            'sections' => $sections,
        ]);
    }

    /**
     * Liste les sections de la page d'accueil pour les réorganisées.
     * @Route("/webapp/section/homepagefavourites", name="op_webapp_section_homepagefavourites", methods={"GET"})
     */
    public function HomepageFavourites()
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));

        return $this->render('webapp/page/homepagefavourites.html.twig', [
            'sections' => $sections
        ]);
    }

    /**
     * @Route("/webapp/section/new", name="op_webapp_section_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_section_index');
        }

        return $this->render('webapp/section/new.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/newbypage/{page}", name="op_webapp_section_newbypage", methods={"GET","POST"})
     */
    public function newbypage(Request $request, $page): Response
    {

        $section = new Section();

        $form = $this->createForm(Section2Type::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_section_index');
        }

        return $this->render('webapp/section/new2.html.twig', [
            'section' => $section,
            'page'=> $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/addsection/{page}/{row}", name="op_webapp_section_add", methods={"GET","POST"})
     */
    public function addSection( $page, $row, EntityManagerInterface $em) : Response
    {
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);
        $position = $row +1;

        $section = new Section;
        $section->setTitle('Nouvelle section');
        $section->setContentType('none');
        $section->setPage($element);
        $section->setPosition($position);
        $em->persist($section);
        $em->flush();

        $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);

        return $this->json([
            'code'          => 200,
            'message'       => 'LA section à bien été ajoutée.',
            'liste'         =>  $this->renderView('webapp/section/include/_liste.html.twig', [
                'sections' => $sections
            ])
        ], 200);
    }

    /**
     * @Route("/webapp/section/{id}", name="op_webapp_section_show", methods={"GET"})
     */
    public function show(Section $section): Response
    {
        return $this->render('webapp/section/show.html.twig', [
            'section' => $section,
        ]);
    }

    /**
     * @Route("/webapp/section/{id}/edit", name="op_webapp_section_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Section $section, EntityManagerInterface $em): Response
    {
        $page = $section->getPage();
        $idpage = $page->getId();
        //dd($idpage);
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('op_webapp_section_edit', [
                'id' => $section->getId()
            ]);
        }

        return $this->render('webapp/section/edit.html.twig', [
            'section' => $section,
            'idpage'=> $idpage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/{id}", name="op_webapp_section_delete", methods={"POST"})
     */
    public function delete(Request $request, Section $section): Response
    {
        if ($this->isCsrfTokenValid('delete'.$section->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($section);
            $entityManager->flush();
        }

        return $this->redirectToRoute('op_webapp_section_index');
    }

    /**
     * Permet d'activer ou de désactiver la mise en vedette d'une section sur la page d'accueil
     * @Route("/webapp/section/jsstar/{id}/json", name="op_webapp_section_star")
     */
    public function jsstar(Section $section, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $isstar = $section->getFavorites();
        // renvoie une erreur car l'utilisateur n'est pas connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // Si la page est déja publiée, alors on dépublie
        if($isstar == true){
            $section->setfavorites(0);
            $em->flush();
            return $this->json([
                'code'      => 200,
                'message'   => "La section n'est plus publiée sur la page d'acccueil."
            ], 200);
        }
        // Si la page est déja dépubliée, alors on publie
        $section->setFavorites(1);
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => "La section est publiée sur la page d'acccueil."
        ], 200);
    }

    /**
     * Supprimer la section
     * @Route("/webapp/section/jsdel/{id}", name="op_webapp_section_del")
     */
    public function jsdel(Section $section, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // code de suppression
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($section);
        $entityManager->flush();
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => "La section est correctement supprimée."
        ], 200);
    }

    /**
     * @Route("/webapp/section/del/{id}", name="op_webapp_section_del", methods={"POST"})
     */
    public function DelEvent(Request $request, Section $section, EntityManagerInterface $em) : Response
    {
        // creation des éléement ncessaire à la méthode
        $user = $this->getUser();
        $page = $section->getPage();

        // Suppression de l'entité
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($section);
        $entityManager->flush();

        // Récupération de la liste des sectiosn
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);
        $sections = $em->getRepository(Section::class)->findbypage($page);
        return $this->json([
            'code'=> 200,
            'message' => "L'évènenemt a été supprimé",
            'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                'sections' => $sections,
            ])
        ], 200);
    }

    /**
     * Permet de déplacer une section dans la liste
     * @Route("/webapp/section/position/{id}/{level}", name="op_webapp_section_position_down")
     */
    public function Position(Section $section, $level, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $id = $section->getId();
        $page = $section->getPage();

        // On récupére la position de la page actuelle et on prépare des les futures positions +1 et -1.
        $position = $section->getPosition();
        $nextPos = $section->getPosition()+1;
        $previousPos = $section->getPosition()-1;

        if($level == 'up')
        {
            $previousItem = $em->getRepository(Section::class)->findOneBy(array('position' => $previousPos));
            $section->setPosition($previousPos);
            $previousItem->setPosition($position);
            $em->flush();

            // on récupère la liste des pages ordonnée par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);

            // on retourne au format JSON
            return $this->json([
                'code'=> 200,
                'message' => "La page est montée d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }elseif($level == 'down'){
            $nextItem = $em->getRepository(Section::class)->findOneBy(array('position' => $nextPos));
            $section->setPosition($nextPos);
            $nextItem->setPosition($position);
            $em->flush();
            // on récupère la liste des pages ordonnée par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);
            return $this->json([
                'code'=> 200,
                'message' => "La page est descendu d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }else{
            return $this->json([
                'code'=> 200,
                'message' => "Une erreur a été détecté",
            ], 200);
        }
    }

    /**
     * Permet de déplacer une section dans la liste
     * @Route("/webapp/section/favorite/position/{id}/{level}", name="op_webapp_section_favorite_position_down")
     */
    public function PositionFavorite(Section $section, $level, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $id = $section->getId();
        $page = $section->getPage();

        // On récupére la position de la page actuelle et on prépare des les futures positions +1 et -1.
        $position = $section->getPositionfavorite();
        $nextPos = $section->getPositionfavorite()+1;
        $previousPos = $section->getPositionfavorite()-1;

        //dd($position, $nextPos, $previousPos);

        if($level == 'up')
        {
            $previousItem = $em->getRepository(Section::class)->findOneBy(array('positionfavorite' => $previousPos));
            //dd($section);
            $section->setPositionfavorite($previousPos);
            $previousItem->setPositionfavorite($position);
            $em->flush();

            // on récupère la liste des pages ordonnée par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));

            // on retourne au format JSON
            return $this->json([
                'code'=> 200,
                'message' => "La page est montée d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }elseif($level == 'down'){
            $nextItem = $em->getRepository(Section::class)->findOneBy(array('positionfavorite' => $nextPos));
            $section->setPositionfavorite($nextPos);
            $nextItem->setPositionfavorite($position);
            $em->flush();
            // on récupère la liste des pages ordonnée par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));
            return $this->json([
                'code'=> 200,
                'message' => "La page est descendu d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }else{
            return $this->json([
                'code'=> 200,
                'message' => "Une erreur a été détecté",
            ], 200);
        }
    }
}
