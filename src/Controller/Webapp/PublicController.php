<?php

namespace App\Controller\Webapp;

use App\Entity\Admin\Parameter;
use App\Entity\Webapp\Section;
use App\Form\Gestapp\SearchProductType;
use App\Repository\Gestapp\ProductRepository;
use App\Repository\Webapp\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PublicController
 * @package App\Controller\Webapp
 */
class PublicController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/", name="op_webapp_public_index")
     */
    public function index(): RedirectResponse
    {
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->findFirstReccurence();
        // boucle : verifie si le site est installé
        if(!$parameter){
            return $this->redirectToRoute('op_admin_dashboard_first_install');
        }
        else {
            $isOnline = $parameter->getIsOnline();
            if(!$isOnline) {
                return $this->redirectToRoute('op_webapp_public_offline');
            }
            else{
                return $this->redirectToRoute('op_webapp_public_homepage');
            }
        }

    }

    /**
     * @return Response
     * Inclus la balise meta du iste pour le référencement
     */
    public function meta() : Response
    {
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->find(1);

        return $this->render('include/meta.html.twig', [
            'parameter' => $parameter
        ]);
    }

    /**
     * @Route("/webapp/public/page/", name="op_webapp_public_homepage")
     */
    public function homepage() : Response
    {
        $uuid = Uuid::v1();
        if (PHP_SESSION_NONE === session_status()) {
            $session = $this->requestStack->getSession();
            $session->set('name_uuid', $uuid);
        }
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->find(1);
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('position' => 'ASC'));

        // integration du code sélectionnant les sections classées comme favorites
        return $this->render('webapp/public/index.html.twig',[
            'parameter' => $parameter,
            'sections' => $sections,
            'session' => $session
        ]);
    }

    /**
     * @Route("/webapp/public/adhesionreply/", name="op_webapp_public_adhesionreply")
     */
    public function adhesionreply() : Response
    {
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->find(1);

        // integration du code sélectionnant les sections classées comme favorites
        return $this->render('webapp/public/adhesionreply.html.twig',[
            'parameter' => $parameter,
        ]);
    }

    /**
     * @route("/webapp/public/offline", name="op_webapp_public_offline")
     */
    public function Offline() : Response
    {
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->findFirstReccurence();
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1));
        $isOnline = $parameter->getIsOnline();

        if ($isOnline == 1){
            return $this->render('webapp/public/index.html.twig', [
                'parameter' => $parameter,
                'sections' => $sections,
            ]);
        }
        return $this->render('webapp/public/Offline.html.twig', [
            'parameter' => $parameter
        ]);
    }

    /**
     * @Route ("/webapp/public/menus/{route}", name="op_webapp_public_listmenus")
     */
    public function BlocMenu(PageRepository $pageRepository, Request $request, $route, ProductRepository $productRepository, PaginatorInterface $paginator): Response
    {
        // on récupère l'utilisateur courant
        $user = $this->getUser();

        // préparation des éléments d'interactivité du menu
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->findFirstReccurence();
        $menus = $pageRepository->listMenu();

        // Module de recherche sur le menu
        $form = $this->createForm(SearchProductType::class, [
            'action' => $this->generateUrl('op_gestapp_product_index'),
            'method' => 'POST'
        ]);
        $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $productRepository->searchProduct($search->get('word')->getData());
            $products = $paginator->paginate(
                $data,
                $request->query->getInt('page', 1),
                18
            );

            return $this->json([
                'code'          => 200,
                'message'       => "Votre recherche comporte x résultats.",
                'count'         => $this->renderView('gestapp/product/include/_count.html.twig', [
                    'products' => $products,
                ])
            ], 200);
        }

        return $this->render('include/navbar_webapp.html.twig', [
            'parameter' => $parameter,
            'menus' => $menus,
            'form' => $form->createView(),
            'route' => $route
        ]);
    }

    /**
     * @Route ("/webapp/public/footer/", name="op_webapp_public_blocfooter")
     */
    public function BlocFooter(PageRepository $pageRepository, Request $request): Response
    {
        // préparation des éléments d'interactivité du menu
        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->findFirstReccurence();
        $pages = $pageRepository->listpage();

        return $this->render('include/footer_webapp.html.twig', [
            'parameter' => $parameter,
            'pages' => $pages,
        ]);
    }

    /**
     * Affiche l'espace clientelle
     * @Route ("/webapp/public/dashboard/client", name="op_webapp_public_dashboard_client", methods={"GET"})
     */
    public function clientDashboard(){

        $user = $this->getUser();

        $parameter = $this->getDoctrine()->getRepository(Parameter::class)->findFirstReccurence();
        return $this->render("webapp/public/clientdashboard.html.twig",[
            'parameter' => $parameter,
            'user' => $user
        ]);
    }
}
