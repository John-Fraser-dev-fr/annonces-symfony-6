<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function inscription(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        //Création d'un nouvel objet User
        $user = new User();
        //Création du formulaire relié à l'entité User
        $formUser = $this->createForm(UserType::class, $user);

        //Analyse de la requête
        $formUser->handleRequest($request);

        if($formUser->isSubmitted() && $formUser->isValid())
        {
            //Hachage mot de passe
            $hash = $encoder->hashPassword($user,$user->getPassword());
            $user->setPassword($hash);

            //Enregistrement en BDD
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre inscription a bien été pris en compte !');
            return $this->redirectToRoute('connexion');
        }

        return $this->render('user/inscription.html.twig', [
            'formUser'=> $formUser->createView()
        ]);
    }



    #[Route('/connexion', name: 'connexion')]
    public function connexion(AuthenticationUtils $auth): Response
    {
        //Obtenir une erreur de connexion s'il y en a une
        $error = $auth->getLastAuthenticationError();

        //Dernier nom entré par l'utilisateur
        $lastUsername = $auth->getLastUsername();

        return $this->render('user/connexion.html.twig', [
            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }


    #[Route('/deconnexion', name:'deconnexion')]
    public function deconnexion()
    {
        return $this->redirectToRoute('app_annonces');
    }

    
}
