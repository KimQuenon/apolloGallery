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
            ->add('firstName', TextType::class, $this->getConfiguration("Nom", "Ex: Jean..."))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Ex: Dupont..."))
            ->add('email', EmailType::class, $this->getConfiguration("Mail", "Ex: jean.dupont@gmail.com..."))
            ->add('message', TextareaType::class, $this->getConfiguration("Message", "Commencez à taper votre message..."))
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
