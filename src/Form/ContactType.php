<?php

namespace App\Form;

use App\Entity\Contact;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, $this->getConfiguration("Name", "Ex: Doe..."))
            ->add('firstName', TextType::class, $this->getConfiguration("Surname", "Ex: John..."))
            ->add('email', EmailType::class, $this->getConfiguration("Mail", "Ex: john.doe@gmail.com..."))
            ->add('message', TextareaType::class, $this->getConfiguration("Message", "Write it out..."))
        ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
