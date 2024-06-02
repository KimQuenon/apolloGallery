<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
        //integertype -> integer
        //numbertype -> float
        $builder
            ->add('title', TextType::class, $this->getConfiguration("Title of the frame:", 'Exemple : Mona Lisa'))
            ->add('artistName', TextType::class, $this->getConfiguration("Last Name:", 'Exemple : Da Vinci'))
            ->add('artistSurname', TextType::class, $this->getConfiguration("First Name:", 'Exemple : Leonardo'))
            ->add('slug', TextType::class, $this->getConfiguration('Slug:', 'URL (generated automatically)',[
                'required' => false
            ]))
            ->add('year', IntegerType::class, $this->getConfiguration("Year:", 'Exemple : 1999'))
            ->add('canvaHeight', NumberType::class, $this->getConfiguration("Canva Height (cm):", 'Exemple : 200'))
            ->add('canvaWidth', NumberType::class, $this->getConfiguration("Canva Width (cm):", 'Exemple : 200'))
            ->add('content', TextareaType::class, $this->getConfiguration("Description:", 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'))
            ->add('priceInit', MoneyType::class, $this->getConfiguration("Initial price:", 'Exemple : 10.000'))
            ->add('endDate', DateType::class, $this->getConfiguration("End date:","End date",[
                'widget' => 'single_text',
            ]))

            ->add('medium', ChoiceType::class, [
                'choices'=>[
                    //options = label, value = database
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
        //add this field except on edit mode
        if (!$options['is_edit']) {
            $builder->add('coverImage', FileType::class, [
                'label' => "Artwork's cover (jpg, png, webp)",
                'required' => false,
                'data' => $options['is_edit'] ? $artwork->getCoverImage() : null,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artwork::class,
            'is_edit' => false, 
        ]);
    }
}
