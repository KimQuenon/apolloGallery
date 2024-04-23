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
            ->add('firstName', TextType::class, $this->getConfiguration("First name", "John"))
            ->add('lastName', TextType::class, $this->getConfiguration("Last name", "Doe"))
            ->add('email', EmailType::class, $this->getConfiguration("Mail", "john.doe@gmail.com"))
            ->add('password', PasswordType::class, $this->getConfiguration("Password", "••••••••••"))
            ->add('passwordConfirm', PasswordType::class, $this->getConfiguration('Confirm your password', "••••••••••"))
            ->add('picture', FileType::class,[
                'label'=>"Avatar (jpg, png, gif)",
                'required'=>false
            ])
            ->add('description', TextareaType::class, $this->getConfiguration("Bio", "Type in some interesting facts about you..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
