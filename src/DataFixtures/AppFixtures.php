<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Artwork;
use App\Entity\Movement;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $movements = [];
        for ($m = 1; $m <= 10; $m++) {
            $movement = new Movement();
            $movement->setMovementName($faker->word());
            $manager->persist($movement);
            $movements[] = $movement;
        }

        
        for($i=1; $i<=30; $i++)
        {
            $artwork = new Artwork();
            $media = ['Oil on canvas','Acrylic','Watercolor', 'Sketch', 'Gouache', 'Encaustic', 'Tempera', 'Pastel', 'Spray', 'Ink', 'Other'];

            $artwork->setTitle($faker->sentence())
                ->setArtistName($faker->lastName())
                ->setArtistSurname($faker->firstName())
                ->setYear($faker->year())
                ->setCanvaWidth($faker->randomFloat(2, 50, 1000))
                ->setCanvaHeight($faker->randomFloat(2, 50, 1000))
                ->setContent('<p>'.join('</p><p>', $faker->paragraphs(5)).'</p>')
                ->setMedium($media[array_rand($media)])
                ->setPriceInit($faker->randomFloat(2, 1000, 100000))
                ->setSubmissionDate($faker->dateTimeBetween('-1 year', '-1 month'))
                ->setEndDate($faker->dateTimeBetween('-1 month', '+1 year'))
                ->setCoverImage('https://picsum.photos/seed/picsum/1000/350');

            $movementsAssociated = $faker->randomElements($movements, $faker->numberBetween(1, 3));

            foreach ($movementsAssociated as $movement) {
                $artwork->addMovement($movement);
            }

            $manager->persist($artwork);
        }


        $manager->flush();
    }
}
