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
    public function index(AnnonceRepository $repo, ImageRepository $repo2): Response
    {

        $annonces = $repo->findAll();


        return $this->render('annonces/index.html.twig', [
            'controller_name' => 'AnnoncesController',
            'annonces'=> $annonces,
        ]);
    }
}
