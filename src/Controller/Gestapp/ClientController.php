<?php

namespace App\Controller\Gestapp;

use App\Entity\Admin\Member;
use App\Form\Admin\ClientType;
use App\Repository\Admin\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientController extends AbstractController
{
    /**
     * @Route("/gest/app/client", name="Gestapp_client")
     */
    public function index(): Response
    {
        return $this->render('gestapp/client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    /**
     * @Route("/admin/client/", name="op_admin_client_liste", methods={"GET"})
     */
    public function client(MemberRepository $memberRepository): Response
    {
        return $this->render('gestapp/client/client.html.twig', [
            'members' => $memberRepository->findBy(array("type" => "client")),
        ]);
    }

    /**
     * @Route("/webapp/client/new", name="op_webapp_client_new", methods={"GET","POST"})
     */
    public function clientNew(Request $request,UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response
    {
        $client = new Member();
        $client->setType('client');
        $client->setRoles(["ROLE_CLIENT"]);
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setPassword(
                $passwordEncoder->encodePassword(
                    $client,
                    $form->get('password')->getData()
                )
            );
            $client->setIsVerified(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();

            // partie de code pour envoyer un email au client
            //$email = (new Email())
            //    ->from('postmaster@openpixl.fr')
            //    ->to($client->getEmail())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
            //    ->subject('JUSTàFaire - Inscription')
                //->text('Sending emails is fun again!')
            //    ->html('
            //        <h1>Cartes de prières<small> - Création de votre compte client</small></h1>
            //        <hr>
            //        <p>Bienvenue, '. $client->getFirstName() .' '.$client->getLastName().'</p>
            //        <p>Vous venez de créer votre compte client sur notre site. Nous vous remercions de votre confiance ezt de l\'interet que vous manifestez à nos produits artisanals.</p>
            //        ');
            //$mailer->send($email);

            return $this->redirectToRoute('op_admin_client_liste_index');
        }

        return $this->render('admin/member/newclient.html.twig', [
            'member' => $client,
            'form' => $form->createView(),
        ]);
    }
}
