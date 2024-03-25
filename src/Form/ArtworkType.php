<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArtworkType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //integertype -> nombre entier
        //numbertype -> nombre décimal
        $builder
            ->add('title', TextType::class, $this->getConfiguration("Title of the frame:", 'Exemple : Giulia'))
            ->add('artistName', TextType::class, $this->getConfiguration("Last Name:", 'Exemple : Verdi'))
            ->add('artistSurname', TextType::class, $this->getConfiguration("First Name:", 'Exemple : Giulia'))
            ->add('slug', TextType::class, $this->getConfiguration('Slug:', 'URL (generated automatically)',[
                'required' => false
            ]))
            ->add('year', IntegerType::class, $this->getConfiguration("Year:", 'Exemple : 1999'))
            ->add('canvaWidth', NumberType::class, $this->getConfiguration("Canva Width (cm):", 'Exemple : 200'))
            ->add('canvaHeight', NumberType::class, $this->getConfiguration("Canva Height (cm):", 'Exemple : 200'))
            ->add('content', TextareaType::class, $this->getConfiguration("Description:", 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'))
            ->add('priceInit', MoneyType::class, $this->getConfiguration("Initial price:", 'Exemple : 100'))
            ->add('submissionDate', null, [
                'label' => 'Submission Date:',
                'widget' => 'single_text',
                //date du jour
                'data' => new \DateTime(),
                // rend le champ non modifiable
                'disabled' => true,
            ])
            ->add('endDate', DateType::class, $this->getConfiguration("End date:","date fin",[
                'widget' => 'single_text',
            ]))
            ->add('coverImage', FileType::class,[
                'label'=>'Artwork (jpg or webp)'])
            ->add('medium', ChoiceType::class, [
                'choices'=>[
                    //options = visuel, valeur = dans la bdd
                    'Oil on canvas'=>'Oil on canvas',
                    'Acrylic'=>'Acrylic',
                    'Watercolor'=>'Watercolor',
                    'Sketch' => 'Sketch',
                    'Gouache'=>'Gouache',
                    'Encaustic'=>'Encaustic',
                    'Tempera'=>'Tempera',
                    'Pastel'=>'Pastel',
                    'Spray'=>'Spray',
                    'Ink'=>'Ink',
                    'Other'=>'Other'
                ]])
            ->add('movements', EntityType::class, [
                'class' => Movement::class,
                'choice_label' => 'movementName',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artwork::class,
        ]);
    }
}
