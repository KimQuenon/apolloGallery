<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Faq;
use App\Entity\User;
use App\Entity\Artwork;
use App\Entity\Movement;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $passwordHasher;

    /**
     * Hashage du mdp
     *
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($f =1; $f <= 5; $f++){
            $faq = new Faq();
            $faq->setQuestion($faker->sentence())
                ->setContent($faker->paragraph());
            $manager->persist($faq);
        }

        $movements = [];
        for ($m = 1; $m <= 10; $m++) {
            $movement = new Movement();
            $movement->setMovementName($faker->word());
            $manager->persist($movement);
            $movements[] = $movement;
        }

        $users = []; //init d'un tab pour recup des users pour les annonces

        for($u = 1; $u <= 10; $u++)
        {
            $user = new User();
            $createdAt = $faker->dateTimeBetween('-1 year', '-1 month');
            $hash = $this->passwordHasher->hashPassword($user, 'password');

            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setCreatedAt(new \DateTimeImmutable($createdAt->format('Y-m-d H:i:s')))
                ->setEmail($faker->email())
                ->setDescription('<p>'.join('<p></p>',$faker->paragraphs(3)).'</p>')
                ->setPassword($hash)
                ->setPicture('');

                $manager->persist($user);

                $users[] = $user; //ajouter un user au tableau pour les annonces
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
                ->setCoverImage('https://picsum.photos/seed/picsum/1000/350')
                ->setAuthor($users[rand(0, count($users)-1)]);

            $movementsAssociated = $faker->randomElements($movements, $faker->numberBetween(1, 3));

            foreach ($movementsAssociated as $movement) {
                $artwork->addMovement($movement);
            }

            $manager->persist($artwork);
        }


        $manager->flush();
    }
}
