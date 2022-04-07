<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AnnonceEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre', TextType::class)
        ->add('description', TextareaType::class)
        ->add('nb_pieces', IntegerType::class)
        ->add('surface', IntegerType::class)
        ->add('prix', IntegerType::class)
        ->add('cd_postal', IntegerType::class)
        ->add('ville', TextType::class)
        ->add('type', TextType::class)
        ->add('imageCover', FileType::class,[
            'data_class' => null,
            'required' => false,
            'mapped' => false
        ])
        ->add('images', FileType::class,[
            'multiple' => true,
            'mapped' => false,
            'required' => false,
            'data_class' => null
            ])
        ->add('valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
