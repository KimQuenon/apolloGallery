<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom est..."))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom de famille est..."))
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Votre adresse e-mail est..."))
            ->add('password', PasswordType::class, $this->getConfiguration("Mot de passe", "Votre mot de passe"))
            ->add('passwordConfirm', PasswordType::class, $this->getConfiguration('Confirmation de votre mot de passe', 'Veuillez confirmer votre mot de passe'))
            ->add('picture', FileType::class,[
                'label'=>"Avatar (jpg, png, gif)",
                'required'=>false
            ])
            ->add('description', TextareaType::class, $this->getConfiguration("Description détaillée", "Il est temps de raconter votre vie..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
