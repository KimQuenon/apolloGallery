<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Review;
use App\Entity\Artwork;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReviewType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('rating', IntegerType::class, $this->getConfiguration("Note sur 5", "Veuillez indiquer votre note de 0 à 5", [
            'attr' => [
                'step' => 1,
                'min' => 0,
                'max' => 5
            ]
        ]))
        ->add('content', TextareaType::class, $this->getConfiguration("Votre avis/ témoignage", "Commencez à écrire..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
