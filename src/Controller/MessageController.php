<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour consulter vos messages !');
            return $this->redirectToRoute('app_annonces');
        } else {
            return $this->render('message/index.html.twig', [
                'controller_name' => 'MessageController',
            ]);
        }
    }


    #[Route('/message/add/{id}', name: 'add_message')]
    public function add(UserRepository $repoUser, $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour envoyer un message !');
            return $this->redirectToRoute('app_annonces');
        } else {
            //Récupére le destinataire
            $dest = $repoUser->find($id);
            //Récupére l'expéditeur
            $exp = $this->getUser();

            //Création d'un nouvel objet Message
            $message = new Message();
            //Formulaire relié à l'entité Message
            $formMessage = $this->createForm(MessageType::class, $message);

            //Analyse de la requete
            $formMessage->handleRequest($request);

            if ($formMessage->isSubmitted() && $formMessage->isValid()) {
                $message->setDestinataire($dest)
                    ->setExpediteur($exp)
                    ->setDate(new \DateTime());

                //Enregistrement en BDD
                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Félicitation, votre message a bien été envoyé !');
                return $this->redirectToRoute('app_annonces');
            }

            return $this->render('message/add.html.twig', [
                "formMessage" => $formMessage->createView(),
                "dest" => $dest
            ]);
        }
    }

    #[Route('/message/received', name: 'received_message')]
    public function received(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour consulter vos messages !');
            return $this->redirectToRoute('app_annonces');
        } else {
            return $this->render('message/received.html.twig');
        }
    }


    #[Route('/message/sended', name: 'sended_message')]
    public function sended(): Response
    {   
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour consulter vos messages !');
            return $this->redirectToRoute('app_annonces');
        } else {
            return $this->render('message/sended.html.twig');
        }
    }

    #[Route('/message/show/{id}', name: 'show_message')]
    public function showMessage($id, MessageRepository $repoMsg): Response
    {
        //récupére le message
        $message = $repoMsg->find($id);
        //Récupére l'email de l'expéditeur
        $exp = $message->getExpediteur();
        //Récupére le destinataire
        $dest = $message->getDestinataire();

        //Si le message n'existe pas 
        if (!$message) {
            $this->addFlash('danger', 'Ce message n\'existe pas !');
            return $this->redirectToRoute('app_message');
        }

        //Si l'utilisateur est égale à l'expéditeur OU le destinataire 
        if ($this->getUser() == $exp or $this->getUser() == $dest) {
            return $this->render('message/show.html.twig', [
                'message' => $message
            ]);
        } else {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de lire ce message !');
            return $this->redirectToRoute('app_annonces');
        }
    }

    #[Route('/message/delete/{id}', name: 'delete_message')]
    public function deleteMessage($id, MessageRepository $repoMsg, EntityManagerInterface $entityManager): Response
    {
        //Récupére le messsage 
        $message = $repoMsg->find($id);
        //Récupére l'email de l'expéditeur
        $exp = $message->getExpediteur();
        //Récupére le destinataire
        $dest = $message->getDestinataire();

        //Si l'utilisateur est égale à l'expéditeur OU le destinataire 
        if ($this->getUser() == $exp or $this->getUser() == $dest) {
            //Supprime le message de la BDD
            $entityManager->remove($message);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été supprimé !');
            return $this->redirectToRoute('app_message');
        } else {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de supprimer ce message ! ');
            return $this->redirectToRoute('app_annonces');
        }

        return $this->render('message/index.html.twig', [
            'message' => $message,
        ]);
    }
}
