<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
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
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    #[Route('/message/add/{id}', name: 'add_message')]
    public function add(UserRepository $repoUser, $id, Request $request, EntityManagerInterface $entityManager): Response
    {
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

        if ($formMessage->isSubmitted() && $formMessage->isValid())
        {
            $message->setDestinataire($dest)
                    ->setExpediteur($exp)
                    ->setDate(new \DateTime())
            ;

            //Enregistrement en BDD
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Félicitation, votre message a bien été envoyé !');
            return $this->redirectToRoute('app_annonces');
        }

        return $this->render('message/add.html.twig', [  
            "formMessage"=> $formMessage->createView() 
        ]);
    }

    #[Route('/message/received', name: 'received_message')]
    public function received(): Response
    {
        return $this->render('message/received.html.twig', [
        ]);
    }

    #[Route('/message/sended', name: 'sended_message')]
    public function sended(): Response
    {
        return $this->render('message/sended.html.twig', [
        ]);
    }
}
