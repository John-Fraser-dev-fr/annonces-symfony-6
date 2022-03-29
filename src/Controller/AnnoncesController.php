<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Form\ImageType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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
    public function add(Request $request, EntityManagerInterface $entityManager, UserRepository $user,  SluggerInterface $slugger): Response
    {   
        //Création d'un nouvel objet Annonce
        $annonce = new Annonce();
        //Formulaire relié à l'entité Annonce
        $formAnnonce = $this->createForm(AnnonceType::class, $annonce);

        //Analyse de la requete
        $formAnnonce->handleRequest($request);

        if($formAnnonce->isSubmitted() && $formAnnonce->isValid())
        {
            //Récupere l'image cover transmise
            $annonceFile = $formAnnonce->get('imageCover')->getData();

            if($annonceFile)
            {
                //Génére un nouveau nom de fichier pour l'image de couverture
                $fichierImageCover = md5(uniqid()).'.'.$annonceFile->guessExtension();
                
                //Envoie du fichier dans public/images
                $annonceFile->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImageCover
                );
             
                $annonce->setImageCover($fichierImageCover);
            }


            //Récupére le(s) image(s)
            $images = $formAnnonce->get('images')->getData();

            //Boucle sur les images
            foreach($images as $image)
            {
                //Génére un nouveau nom de fichier pour les images
                $fichierImages = md5(uniqid()).'.'.$image->guessExtension();

                //Envoie des images dans public/images
                $image->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImages
                );

                
                //Création d'un nouvel objet Image
                $img = new Image();

                
                $img->setImage($fichierImages)
                    ->setAnnonce($annonce)
                ;
                    
                //Enregistrement en  BDD
                $entityManager->persist($img);  
                
            }


            //Récupération de l'id user
            $user = $this->getUser();

        
            $annonce->setDate(new \DateTime())
                    ->setUser($user)
                    ->addImage($img)                    
            ;

            //Enregistrement en BDD
            $entityManager->persist($annonce);
          
            $entityManager->flush();


            return $this->redirectToRoute('app_annonces');
        }


        return $this->render('annonces/add.html.twig',[
            'formAnnonce'=> $formAnnonce->createView(),
            
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

    #[Route('/mes_annonces/{id}', name: 'annonceByUser')]
    public function annoncesByUser(AnnonceRepository $repo, $id, ImageRepository $repo2, UserRepository $repo3)
    {
        //Récupére les annonces correspondant à l'utilisateur
        $annoncesByUser = $repo->findBy(['user' => $id]);


        return $this->render('user/annonces.html.twig',[
            'annoncesByUsers' => $annoncesByUser,
           
        ]);
    }

}




