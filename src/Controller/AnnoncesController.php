<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnoncesController extends AbstractController
{
    #[Route('/index', name: 'app_annonces')]
    public function index(AnnonceRepository $repo): Response
    {
        //Récupére toutes les annonces
        $annonces = $repo->findAll();

        return $this->render('annonces/index.html.twig', [
            'annonces'=> $annonces,
        ]);
    }


    #[Route('/add', name: 'add_annonce')]
    public function add(Request $request, EntityManagerInterface $entityManager, UserRepository $user): Response
    {   
        //Création d'un nouvel objet Annonce
        $annonce = new Annonce();

        //Formulaire relié à l'entité Annonce
        $formAnnonce = $this->createForm(AnnonceType::class, $annonce);

        //Analyse de la requete
        $formAnnonce->handleRequest($request);

        if($formAnnonce->isSubmitted() && $formAnnonce->isValid())
        {

            //Récupération de l'id user
            $user = $this->getUser();

            $annonce->setDate(new \DateTime())
                    ->setUser($user);
           

            //Enregistrement en BDD
            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('app_annonces');
        }


        return $this->render('annonces/add.html.twig',[
            'formAnnonce'=> $formAnnonce->createView()
        ]);
    }


    #[Route('/annonce/{id}', name: 'show_annonce')]
    public function show(AnnonceRepository $repo, $id, ImageRepository $repo2)
    {
        //Récupére l'annonce grace à l'ID (GET)
        $annonce = $repo->find($id); 

        //Récupére les images correspondantes à l'ID de l'annonce
        $images = $repo2->findBy(['annonce'=>$id]); 

        return $this->render('annonces/show.html.twig',[
            'annonce' => $annonce,
            'images' => $images,
        ]);
    }

}
