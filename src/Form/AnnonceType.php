<?php

namespace App\Form;


use App\Entity\Annonce;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('nb_pieces', IntegerType::class,[
                'label' => 'Nombre de pièces'
            ])
            ->add('surface', IntegerType::class)
            ->add('prix', IntegerType::class)
            ->add('cd_postal', IntegerType::class,[
                'label' => 'Code postal'
            ])
            ->add('ville', TextType::class)
            ->add('type', TextType::class)
            ->add('imageCover', FileType::class,[
                'label' => 'Image de couverture'
            ])
            ->add('images', FileType::class,[
                'label' => 'Ajouter d\'autres images',
                'multiple' => true,
                'mapped' => false,
                'required' => false
                ])
            ->add('valider', SubmitType::class)

            
        ;
    }

    
}
