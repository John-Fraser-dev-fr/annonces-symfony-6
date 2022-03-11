<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnoncesController extends AbstractController
{
    #[Route('/index', name: 'app_annonces')]
    public function index(AnnonceRepository $repo): Response
    {
        $annonces = $repo->findAll();

        return $this->render('annonces/index.html.twig', [
            'controller_name' => 'AnnoncesController',
            'annonces'=> $annonces,
        ]);
    }


    #[Route('/{id}', name: 'show_annonce')]
    public function show(AnnonceRepository $repo, $id, ImageRepository $repo2)
    {
        $annonce = $repo->find($id); 

        $images = $repo2->findBy(['annonce'=>$id]); 

        return $this->render('annonces/show.html.twig',[
            'annonce' => $annonce,
            'images' => $images,
        ]);
    }
}
