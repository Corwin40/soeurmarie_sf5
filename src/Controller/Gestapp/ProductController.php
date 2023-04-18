<?php

namespace App\Controller\Gestapp;

use App\Cart\CartService;
use App\Entity\Gestapp\Product;
use App\Entity\Gestapp\ProductCategory;
use App\Entity\Gestapp\ProductCustomize;
use App\Entity\Gestapp\ProductNature;
use App\Form\Gestapp\ProductType;
use App\Form\Gestapp\SearchProductType;
use App\Repository\Gestapp\ProductCategoryRepository;
use App\Repository\Gestapp\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *
 */
class ProductController extends AbstractController
{
    protected $productRepository;
    protected $cartService;

    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/gestapp/product/", name="op_gestapp_product_index", methods={"GET", "POST"})
     */
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $products = $productRepository->findBy(['ProductCategory' => 2]);

        return $this->render('gestapp/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/gestapp/product/json/{idcategory}", name="op_gestapp_product_indexjson", methods={"GET", "POST"})
     */
    public function indexjson(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request, $idcategory): Response
    {
        $products = $productRepository->findBy(['ProductCategory'=>$idcategory]);

        return $this->json([
            'code'          => 200,
            'message'       => "Le produit a été correctement ajouté.",
            'liste' => $this->renderView('gestapp/product/include/_liste.html.twig', [
                'products' => $products
            ])
        ]);
    }


    /**
     * @Route("/gestapp/product/new", name="op_gestapp_product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $natures = $this->getDoctrine()->getRepository(ProductNature::class)->findAll();
        $categories = $this->getDoctrine()->getRepository(ProductCategory::class)->findAll();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('op_gestapp_product_index');
        }

        return $this->render('gestapp/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'natures' => $natures,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/gestapp/product/duplicate/{id}", name="op_gestapp_product_duplicate", methods={"GET","POST"})
     */
    public function duplicate(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $productduplicate = clone $product;
        $productduplicate->setName('Copie');
        $em->persist($productduplicate);
        $em->flush();

        return $this->redirectToRoute('op_gestapp_product_edit', [
            'id' => $productduplicate->getId()
        ]);
    }

    /**
     * @Route("/webbapp/product/{id}", name="op_gestapp_product_show", methods={"GET"})
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function show(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession()->get('name_uuid');
        // On teste si le panier existe en session
        $cart = $request->getSession()->get('cart');

        if($cart){
            //dd($cart);
            // récupération des items du panier
            $detailedCart = $this->cartService->getDetailedCartItem();
            //dd($detailedCart);
            $productCustomize = $em->getRepository(ProductCustomize::class)->findOneBy(array('product' => $product->getId()), array('id'=>'DESC'));
            // Dans le cas ou le panier existe et contient un produit
            if(!$productCustomize){
                $lsformats = $product->getFormats();
                $format = $lsformats[0];
                // création d'une personnalisation du produit
                $productCustomize = new ProductCustomize();
                $productCustomize->setUuid($session);
                $productCustomize->setName('');
                $productCustomize->setProduct($product);
                $productCustomize->setFormat($format);
                $em->persist($productCustomize);
                $em->flush();

                return $this->render('gestapp/product/show.html.twig', [
                    'product' => $product,
                    'session' => $session,
                    'items' => $detailedCart,
                    'customizes' => $productCustomize
                ]);
            }


            // On retourne la vue du produit avec les éléments du panier
            return $this->render('gestapp/product/show.html.twig', [
                'product' => $product,
                'items' => $detailedCart,
                'session' => $session,
                'customizes' => $productCustomize
            ]);
        }

        return $this->render('gestapp/product/show.html.twig', [
            'product' => $product,
            'session' => $session
        ]);
    }

    /**
     * @Route("/webbapp/product/showjson/{id}", name="op_gestapp_product_showjson", methods={"GET"})
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function showjson(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $detailedCart = $this->cartService->getDetailedCartItem();

        $session = $request->getSession()->get('name_uuid');
        // Récupération des personnalisations du produit
        $productCustomize = $em->getRepository(ProductCustomize::class)->findBy(array('product' => $product->getId()));
        // Retourne une réponse en json
        return $this->json([
            'code'          => 200,
            'message'       => "Le produit a été correctement ajouté.",
            'count'         => $this->renderView('gestapp/product/include/_count.html.twig', [
                'items' => $detailedCart,
                'product' => $product,
                'session' => $session,
                'customizes' => $productCustomize
            ])
        ], 200);
    }

    /**
     * @Route("/gestapp/product/{id}/edit", name="op_gestapp_product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $natures = $em->getRepository(ProductNature::class)->findAll();
        $categories = $em->getRepository(ProductCategory::class)->findAll();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('op_gestapp_product_index');
        }

        return $this->render('gestapp/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'natures' => $natures,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/gestapp/product/{id}", name="op_gestapp_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('op_gestapp_product_index');
    }



    /**
     * Permet d'activer ou de désactiver la mise en ligne d'un produit
     * @Route("/gestapp/product/online/{id}/json", name="op_gestapp_product_online")
     */
    public function jsonline(Product $product, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $isonline = $product->getIsOnLine();
        // renvoie une erreur car l'utilisateur n'est pas connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // Si la page est déja publiée, alors on dépublie
        if($isonline == true){
            $product->setIsOnLine(0);
            $em->flush();
            return $this->json([
                'code'      => 200,
                'message'   => "Le produit est n'est plus publié en ligne."
            ], 200);
        }
        // Si la page est déja dépubliée, alors on publie
        $product->setIsOnLine(1);
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => 'Le produit est mis en ligne.'
        ], 200);
    }

    /**
     * Permet d'activer ou de désactiver la mise en ligne d'un produit
     * @Route("/gestapp/product/jsstar/{id}/json", name="op_gestapp_product_star")
     */
    public function jsstar(Product $product, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $isstar = $product->getIsStar();
        // renvoie une erreur car l'utilisateur n'est pas connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);
        // Si la page est déja publiée, alors on dépublie
        if($isstar == true){
            $product->setIsStar(0);
            $em->flush();
            return $this->json([
                'code'      => 200,
                'message'   => "Le produit est n'est plus publié en vedette."
            ], 200);
        }
        // Si la page est déja dépubliée, alors on publie
        $product->setIsStar(1);
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => 'Le produit est publié en vedette.'
        ], 200);
    }

    /**
     * Espace E-Commerce : Liste les produits
     * @Route("/gestapp/product/alldispo", name="op_gestapp_product_alldispo", methods={"GET","POST"})
     */
    public function ListAllProductDispo()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->listAllProduct();

        return $this->render('gestapp/product/listallproductdispo.html.twig',[
            'products' => $products
        ]);
    }



    /**
     * Espace E-Commerce : Liste les produits selon la nature de ces derniers
     * @Route("/gestapp/product/oneNat/{idnat}", name="op_gestapp_product_onecat", methods={"POST"})
     */
    public function ListOneNatProduct(Request $request, PaginatorInterface $paginator, $idnat, EntityManagerInterface $em)
    {
        $data = $em->getRepository(Product::class)->onenature($idnat);

        $nature = $em->getRepository(ProductNature::class)->find($idnat);
        $categories = $em->getRepository(ProductCategory::class)->findBy(array('Nature'=> $idnat));


        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('gestapp/product/listonenatproduct.html.twig',[
            'products' => $products,
            'categories' => $categories,
            'nat' => $idnat,
            'natorcat' => 'nature'
        ]);
    }

    /**
     * Espace E-Commerce : Renvoie les produits filtrés par nature
     * @Route("/gestapp/product/oneNat/filternature", name="op_gestapp_products_filternature", methods={"GET","POST"})
     */
    public function filternature(Request $request, ProductRepository $productRepository,PaginatorInterface $paginator): Response
    {
        $filters = $request->get("categories");
        $page = $request->get('page');

        $data = $productRepository->ListFilterscategories($filters);

        $products = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            8 /*limit per page*/
        );
        if(!$page){
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', 1),
                ])
            ], 200);
        }else{
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', $page),
                ])
            ], 200);
        }
    }

    /**
     * Espace E-Commerce : Liste les produits selon la catégorie de ces derniers
     * @Route("/gestapp/product/oneCat/{idcat}", name="op_gestapp_product_onecat", methods={"POST"})
     */
    public function ListOneCatProduct(Request $request, PaginatorInterface $paginator, $idcat, EntityManagerInterface $em)
    {
        // on récupère les enfants d'une catégorie parente si elle existe
        $childs = $em->getRepository(ProductCategory::class)->findChilds($idcat);
        if (!$childs){
            $data = $em->getRepository(Product::class)->oneCategory($idcat);
        }
        else{
            $data = $em->getRepository(Product::class)->childCategory($childs);
        }

        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            8
        );

        $category = $em->getRepository(ProductCategory::class)->find($idcat);

        return $this->render('gestapp/product/listonecatproduct.html.twig',[
            'products' => $products,
            'category' => $category,
            'childs' => $childs,
            'idcat'=>$idcat,
            'cat' => $idcat,
            'natorcat' => 'category'
        ]);
    }

    /**
     * Espace Administration : Liste les produits selon la catégorie de ces derniers
     * @Route("/admin/product/oneCat/{idcat}", name="op_admin_product_onecat", methods={"GET"})
     */
    public function ListOneCatProductJson(Request $request, $idcat, EntityManagerInterface $em)
    {
        $products = $em->getRepository(Product::class)->oneCategory($idcat);
        //dd($products);

        return $this->json([
            'code'      => 200,
            'message'   => "Ok",
            'liste' => $this->renderView('gestapp/product/include/_liste.html.twig', [
                'products' => $products,
            ])
        ], 200);
    }

    /**
     * Liste les autres catégorie d'un produit
     * @Route("/gestapp/product/onethercategories", name="op_gestapp_products_othercategories", methods={"GET"})
     */
    public function listOthercategories(Product $product, ProductRepository $productRepository)
    {
        return $this->render('gestapp/product/listothercategories.html.twig',[
            'product' => $product,
        ]);
    }

    /**
     * @Route("/gestapp/product/oneCat/filtercategories", name="op_gestapp_products_filtercategories", methods={"GET","POST"})
     */
    public function filtercategories(Request $request, ProductRepository $productRepository,PaginatorInterface $paginator): Response
    {
        $filters = $request->get("categories");
        $page = $request->get('page');

        $data = $productRepository->ListFilterscategories($filters);

        $products = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            8 /*limit per page*/
        );
        if(!$page){
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', 1),
                ])
            ], 200);
        }else{
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', $page),
                ])
            ], 200);
        }
    }

    /**
     * Espace E-Commerce : Renvoie les produits filtrés par catégories selon la nature.
     * @Route("/gestapp/product/filterwebapp", name="op_gestapp_products_filterwebapp", methods={"GET","POST"})
     */
    public function filterWebapp(Request $request, ProductRepository $productRepository,PaginatorInterface $paginator, EntityManagerInterface $em): Response
    {
        // on récupère les éléments inclus dans l'url
        $filters = $request->get("categories");     // Données issu du formulaire filtrant les catégories disponibles
        $cat = $request->get('category');           // issu de la requete : filtrera les produits selon la catégorie donnée
        $nat = $request->get('nature');          // issu de la requete : filtrera les produits selon la nat donnée
        $page = $request->get('page');              // transmet la page pour l'affichage

        //dd($filters);
        if($filters){
            $data = $productRepository->ListFilterscategories($filters);
            if($nat){
                // on recupère l'entité lié au nom de la nat
                $nature = $em->getRepository(ProductNature::class)->findBy(array('name'=> $nat));
                // On récupére toute les catégories parentes classés dans la nat
                $childs = $em->getRepository(ProductCategory::class)->findBy(array('Nature'=> $nature));

            }
            if($cat){
                // on recupère l'entité lié au nom de la catégorie parente
                $category = $em->getRepository(ProductCategory::class)->findBy(array('name'=> $cat));
                //dd($category);
                // On récupére toute les catégories parentes classés dans la nat
                $childs = $em->getRepository(ProductCategory::class)->findChilds($category);
                //dd($childs);
            }
        }else{
            if($nat){
                $data = $em->getRepository(Product::class)->oneNatureName($nat);
                // on recupère l'entité lié au nom de la nat
                $nature = $em->getRepository(ProductNature::class)->findBy(array('name'=> $nat));
                // On récupére toute les catégories parentes classés dans la nat
                $childs = $em->getRepository(ProductCategory::class)->findBy(array('Nature'=> $nature));
            }else{
                //dd($cat);
                // on recupère l'entité lié au nom de la catégorie parente
                $category = $em->getRepository(ProductCategory::class)->findBy(array('name'=> $cat));
                //dd($category);
                $childs = $em->getRepository(ProductCategory::class)->findChilds($category);
                //dd($childs);
                if (!$childs){
                    $category = $em->getRepository(ProductCategory::class)->findOneBy(['name' => $cat]);
                    //$newchilds = $em->getRepository(ProductCategory::class)->findChilds($category->getId());
                    //$data = $em->getRepository(Product::class)->childCategory($newchilds);
                    $data = $em->getRepository(Product::class)->oneCategory($category);
                }
                else{
                    $data = $em->getRepository(Product::class)->childCategory($childs);
                }
            }
        }
        // Transmission à la variable produit et à la pagination les élément récupérés dans les requètes data
        $products = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            8 /*limit per page*/
        );
        if(!$page){
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'childs'=>$childs,
                    'page' => $request->query->getInt('page', 1),
                ])
            ], 200);
        }else{
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'childs'=>$childs,
                    'page' => $request->query->getInt('page', $page),
                ])
            ], 200);
        }
    }

    /**
     * @Route("/gestapp/product/oneCat/filtercategory", name="op_gestapp_products_filtercategory", methods={"GET","POST"})
     */
    public function filtercategory(Request $request, ProductRepository $productRepository,PaginatorInterface $paginator): Response
    {
        //dd($request->get('category'));
        $filter = $request->get("category");
        $page = $request->get('page');

        $data = $productRepository->ListFilterscategory($filter);
        //dd($data);

        $products = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            8 /*limit per page*/
        );
        if(!$page){
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', 1),
                ])
            ], 200);
        }else{
            return $this->json([
                'code'      => 200,
                'message'   => "Ok",
                'liste' => $this->renderView('gestapp/product/include/_product.html.twig', [
                    'products' => $products,
                    'page' => $request->query->getInt('page', $page),
                ])
            ], 200);
        }
    }


    /**
     * Espace E-Commerce : Liste les produits
     * @Route("/gestapp/product/del/{id}", name="op_gestapp_product_del", methods={"POST"}, requirements={"id":"\d+"})
     */
    public function ProductDel(Product $product, Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        // si utilisateur non connecté
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'êtes pas connecté"
        ], 403);

        // code de suppression
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);
        $entityManager->flush();
        $em->flush();

        // on récupère toute le liste de produits pour le rafraichissement
        $products = $em->getRepository(Product::class)->findAll();

        // Retourne une réponse en json
        return $this->json([
            'code'          => 200,
            'message'       => "Le produit a été correctement supprimé.",
            'liste'         => $this->renderView('gestapp/product/include/_liste.html.twig', [
                'products' => $products
            ])
        ], 200);
    }

    /**
     * Affiche une section contenant les 3 dernières cartes de la nature de produit
     * @Route("/gestapp/product/lastnatproduct/{idnat}", name="op_gestapp_product_del", methods={"POST"}, requirements={"id":"\d+"})
     */
    public function LastNatProduct($idnat, EntityManagerInterface $entityManager)
    {
        $products = $entityManager->getRepository(Product::class)->lastProduct($idnat);

        return $this->render('gestapp/product/lastnatproduct.html.twig',[
            'products' => $products,
            'idnat' => $idnat
        ]);
    }

    /**
     * Affiche les produits favoris
     * @Route("/gestapp/product/favories", name="op_gestapp_product_favories", methods={"POST"})
     */
    public function FavoriesProducts(EntityManagerInterface $entityManager)
    {
        $products = $entityManager->getRepository(Product::class)->favoriesproducts();
        return $this->render('gestapp/product/favories.html.twig', [
            "products" => $products
        ]);
    }

    /**
     * Mise à jor de la position du produit
     * @Route("/gestapp/product/updateposition/{idcol}/{key}", name="op_gestapp_product_updateposition", methods={"POST"})
     */
    public function updateposition(
        ProductRepository $productRepository,
        ProductCategoryRepository $productCategoryRepository,
        $idcol,
        $key,
        EntityManagerInterface $em
    )
    {
        // récupérer le produit correspondant à l'id
        $product = $productRepository->findOneBy(['position' => $idcol]);
        // Créer une position - Nat/Cat/pos
        // on construit la position selon la nature et les catégories
        $idnature = $product->getProductNature()->getId();
        $idcat = $product->getProductCategory()->getId();

        $position = $idnature.'-'.$idcat.'-'.$key;
        // Fixation de la nouvelle position
        $product->setPosition($position);
        $em->persist($product);
        $em->flush();

        return $this->json([
            'code'=> 200,
            'message' => "Les produits ont été correctement déplacés."
        ], 200);
    }
}
