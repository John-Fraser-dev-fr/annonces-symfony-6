<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AnnonceFixtures extends Fixture
{
    public function load(ObjectManager $manager, $min=null, $max=null): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //Création d'utilisateur
        for($i=1;$i<=9;$i++)
        {
            $user = new User();
            $user->setNom($faker->lastName())
                 ->setPrenom($faker->firstName())
                 ->setEmail($faker->email())
                 ->setPassword($faker->password())
                 ->setPhone('0612345678')
            ;
            $manager->persist($user);

            //Création d'annonces lié à un utilisateur
        for($j=1; $j<=mt_rand(1,3); $j++)
        {
            
            $annonce= new Annonce();
            $annonce->setTitre($faker->sentence())
                    ->setDescription($faker->paragraph())
                    ->setNbPieces($faker->numberBetween($min= 1, $max=6))
                    ->setSurface($faker->numberBetween($min= 20, $max= 120))
                    ->setPrix($faker->numberBetween($min=90000, $max=400000))
                    ->setCdPostal($faker->numberBetween($min=12538, $max=87290))
                    ->setVille($faker->city())
                    ->setDate($faker->dateTimeBetween('-6 months'))
                    ->settype($faker->word())
                    ->setUser($user)
            ;

            $manager->persist($annonce);
        }

        //Création d'images relié à une annonce
        for($k=1;$k<=mt_rand(3,6);$k++)
        {
            $image= new Image();
            $image->setImage('http://via.placeholder.com/400x250')
                  ->setAnnonce($annonce)
            ;

            $manager->persist($image);
        }
        }

        





        $manager->flush();

    }
}
