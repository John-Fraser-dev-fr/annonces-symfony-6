<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\AnnonceEditType;
use App\Form\AnnonceType;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AnnoncesController extends AbstractController
{
    #[Route('/index', name: 'app_annonces')]
    public function index(AnnonceRepository $repo): Response
    {
        //Récupére toutes les annonces
        $annonces = $repo->findby([], ['date' => 'desc']);

        return $this->render('annonces/index.html.twig', [
            'annonces' => $annonces,
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

        if ($formAnnonce->isSubmitted() && $formAnnonce->isValid()) {
            //Récupere l'image cover transmise
            $annonceFile = $formAnnonce->get('imageCover')->getData();

            if ($annonceFile) {
                //Génére un nouveau nom de fichier pour l'image de couverture
                $fichierImageCover = md5(uniqid()) . '.' . $annonceFile->guessExtension();

                //Envoie du fichier le folder
                $annonceFile->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImageCover
                );

                $annonce->setImageCover($fichierImageCover);
            }


            //Récupére le(s) image(s)
            $images = $formAnnonce->get('images')->getData();

            //Boucle sur les images
            foreach ($images as $image) {
                //Génére un nouveau nom de fichier pour les images
                $fichierImages = md5(uniqid()) . '.' . $image->guessExtension();

                //Envoie des images dans le folder
                $image->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImages
                );

                //Création d'un nouvel objet Image
                $img = new Image();

                $img->setImage($fichierImages)
                    ->setAnnonce($annonce);

                //Enregistrement en  BDD
                $entityManager->persist($img);
            }

            //Récupération de l'id user
            $user = $this->getUser();

            $annonce->setDate(new \DateTime())
                ->setUser($user)
                ->addImage($img);

            //Enregistrement en BDD
            $entityManager->persist($annonce);

            $entityManager->flush();

            $this->addFlash('success', 'Félicitation, votre annonce a bien été créée !');
            return $this->redirectToRoute('app_annonces');
        }

        return $this->render('annonces/add.html.twig', [
            'formAnnonce' => $formAnnonce->createView(),
        ]);
    }



    #[Route('/annonce/{id}', name: 'show_annonce')]
    public function show(AnnonceRepository $repo, $id, ImageRepository $repo2)
    {
        //Récupére l'annonce grace à l'ID (GET)
        $annonce = $repo->find($id);
        //Récupére les images correspondantes à l'ID de l'annonce
        $images = $repo2->findBy(['annonce' => $id]);

        if(!$annonce) {
            $this->addFlash('danger', 'Cet annonce n\'existe pas !');
            return $this->redirectToRoute('app_annonces');
        }else
        {
            return $this->render('annonces/show.html.twig', [
                'annonce' => $annonce,
                'images' => $images,
            ]);
        }
    }


    #[Route('/mes_annonces', name: 'annoncesByUser')]
    public function annoncesByUser(AnnonceRepository $repo)
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté !');
            return $this->redirectToRoute('app_annonces');
        }
        else 
        {
            //Récupére les annonces correspondant à l'utilisateur
            $annoncesByUsers = $repo->findBy(['user' => $user], ['id' => 'desc']);

            return $this->render('user/annonces.html.twig', [
                'annoncesByUsers' => $annoncesByUsers,
            ]);
        }
    }


    #[Route('/annonces/{id}/edit', name: 'edit_annonce')]
    public function editAnnonce(AnnonceRepository $repo, ImageRepository $repo2, Request $request, $id, EntityManagerInterface $entityManager)
    {
        //Récupére l'annonce concerné
        $annonce = $repo->find($id);
        //récupére l'email du  propriètaire de l'annonce
        $user = $annonce->getUser();
        //récupére l'email de l'utilisateur
        $userCo = $this->getUser();

        if(!$userCo)
        {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier cet annonce !');
            return $this->redirectToRoute('app_annonces');
        }

        if($user != $userCo)
        {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier cet annonce !');
            return $this->redirectToRoute('app_annonces');
        }
        else
        {

        //Récupére les images correspondantes 
        $imagesByAnnonces = $repo2->findBy(['annonce' => $id]);

        //Formulaire relié à l'entité Annonce
        $formEdit = $this->createForm(AnnonceEditType::class, $annonce);

        //Analyse de la requete
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            //Récupere l'image cover transmise
            $annonceFile = $formEdit->get('imageCover')->getData();

            if ($annonceFile) {
                //Récupére le chemin
                $cheminImageCoverSupp = $this->getParameter('annonce_directory') . '/' . $annonce->getImageCover();

                //Si il existe, on supprime du folder
                if (file_exists($cheminImageCoverSupp)) {
                    unlink($cheminImageCoverSupp);
                }

                //Génére un nouveau nom de fichier pour l'image de couverture
                $fichierImageCover = md5(uniqid()) . '.' . $annonceFile->guessExtension();

                //Envoie du fichier dans le folder
                $annonceFile->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImageCover
                );

                $annonce->setImageCover($fichierImageCover);
            } else {
            }


            //Récupére le(s) image(s) transmise(s)
            $images = $formEdit->get('images')->getData();

            //Boucle sur les images
            foreach ($images as $image) {
                //Génére un nouveau nom de fichier pour les images
                $fichierImages = md5(uniqid()) . '.' . $image->guessExtension();

                //Envoie des images dans le folder
                $image->move(
                    $this->getParameter('annonce_directory'),
                    $fichierImages
                );

                //Création d'un nouvel objet Image
                $img = new Image();

                $img->setImage($fichierImages)
                    ->setAnnonce($annonce);

                //Enregistrement en  BDD
                $entityManager->persist($img);
            }

            //Récupération de l'id user
            $user = $this->getUser();

            $annonce->setDate(new \DateTime())
                ->setUser($user);

            $entityManager->persist($annonce);
            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a bien été modifié !');
            return $this->redirectToRoute('app_annonces');
        } else {
        }

        }

        return $this->render('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'imagesByAnnonces' => $imagesByAnnonces,
            'formEdit' => $formEdit->createView()
        ]);
    }



    #[Route('/annonces/image/{id}/supp', name: 'supp_image')]
    public function supprimerImage(ImageRepository $RepoImage, $id, EntityManagerInterface $entityManager)
    {
        //Récupére l'image concerné
        $images = $RepoImage->find($id);
        //récupére le propiétaire de limage
        $userImage = $images->getAnnonce()->getUser();
        //récupére l'utilisateur
        $user = $this->getUser();

        if($userImage != $user)
        {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de supprimer cet image !');
            return $this->redirectToRoute('app_annonces');
        }
        else
        {

        //Récupére le chemin
        $cheminImage = $this->getParameter('annonce_directory') . '/' . $images->getImage();

        //Si il existe, on supprime du folder
        if (file_exists($cheminImage)) {
            unlink($cheminImage);
        }

        //Supprimer de la BDD
        $entityManager->remove($images);
        $entityManager->flush();

        $this->addFlash('danger', 'Votre image a bien été supprimé !');
        return $this->redirectToRoute('edit_annonce', [
            'id' => $images->getAnnonce()->getId()
        ]);

    }
    }



    #[Route('/annonces/{id}/supp', name: 'supp_annonce')]
    public function supprimerAnnonce(AnnonceRepository $repoAnnonce, ImageRepository $repoImage, $id, EntityManagerInterface $entityManager)
    {
        //Récupére l'annonce concerné
        $annonce = $repoAnnonce->find($id);
        //Récupére les images lié a cet annonce
        $images = $repoImage->findBy(['annonce' => $id]);
        //Récupére le propriétaire de l'annonce
        $userAnnonce = $annonce->getUser();
        //Récupére l'utilisateur
        $user = $this->getUser();


        if($userAnnonce != $user)
        {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de supprimer cet image !');
            return $this->redirectToRoute('app_annonces');
        }
        else
        {

        //Récupére l'image de couverture de l'annonce
        $imageCover = $annonce->getImageCover();
        //Récupére le chemin 
        $cheminCoverImage = $this->getParameter('annonce_directory') . '/' . $imageCover;

        //Si il existe, on supprime du folder
        if (file_exists($cheminCoverImage)) {
            unlink($cheminCoverImage);
        }

        //Récupére tous les chemins des image(s) correspondantes
        foreach ($images as $image) {
            $cheminImage = $this->getParameter('annonce_directory') . '/' . $image->getImage();

            //Si existe, on supprime du folder
            if (file_exists($cheminImage)) {
                unlink($cheminImage);
            }
        }

        //Supprimer l'annonce de la BDD
        $entityManager->remove($annonce);
        $entityManager->flush();

        $this->addFlash('danger', 'Votre annonce a bien été supprimé !');

        return $this->redirectToRoute('annoncesByUser');

    }
    }
}


