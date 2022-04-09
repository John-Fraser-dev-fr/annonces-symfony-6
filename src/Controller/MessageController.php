<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\AnnonceRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    #[Route('/mes_messages', name: 'index_message')]
    public function index(MessageRepository $repoMsg, AnnonceRepository $repoAnnonce): Response
    {
        //Récupére l'id de l'utilisateur connecté
        $user = $this->getUser();

        //Récupére tous le(s) message(s) envoyé par cet utilisateur
        $messagesByUsers = $repoMsg->findBy(['user' => $user]);


        
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'messagesByUsers' => $messagesByUsers,
        ]);
    }


    #[Route('/add_message/{id}', name: 'add_message')]
    public function add(AnnonceRepository $repoAnnonce, $id, Request $request, EntityManagerInterface $entityManager)
    {
        //Récupére l'annonce concerné par le message
        $annonce = $repoAnnonce->find($id);
        //Récupére l'id de l'utilisateur qui envoie le message
        $user = $this->getUser();

        //Création d'un nouvel objet Message
        $message = new Message();
        //Formulaire relié à l'entité Message
        $formMessage = $this->createForm(MessageType::class, $message);

        //Analyse de la requete
        $formMessage->handleRequest($request);

        if ($formMessage->isSubmitted() && $formMessage->isValid())
        {
            $message->setUser($user)
                    ->setAnnonce($annonce)
                    ->setDate(new \DateTime())
            ;

            //Enregistrement en BDD
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Félicitation, votre message a bien été envoyé !');
            return $this->redirectToRoute('app_annonces');
        }

        return $this->render('message/add.html.twig', [
            'formMessage' => $formMessage->createView(),
        ]);
        
    }
}
