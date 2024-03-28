<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AccountModifyType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom..."))
        ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom..."))
        ->add('email', EmailType::class, $this->getConfiguration("Mail", "Votre adresse mail..."))
        ->add('description', TextareaType::class, $this->getConfiguration("Description", "Votre description..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
