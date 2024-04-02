<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Faq;
use App\Entity\User;
use App\Entity\Artwork;
use App\Entity\Auction;
use App\Entity\Contact;
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

        // création d'un admin
        $admin = new User();
        $admin->setFirstName('Kim')
            ->setLastName('Possible')
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'))
            ->setEmail('admin@epse.be')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
            ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
            ->setRoles(['ROLE_ADMIN'])
            ->setPicture('');

        $manager->persist($admin);

        $users = []; //init d'un tab pour recup des users pour les annonces

        for($u = 1; $u <= 10; $u++)
        {
            $user = new User();
            $hash = $this->passwordHasher->hashPassword($user, 'password');

            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'))
                ->setEmail($faker->email())
                ->setPassword($hash)
                ->setDescription('<p>'.join('<p></p>',$faker->paragraphs(3)).'</p>')
                ->setPicture('');

                $manager->persist($user);

                $users[] = $user; //ajouter un user au tableau pour les annonces
        }

        $artworks = []; //init d'un tab pour recup des artworks pour les enchères

        for($a=1; $a<=30; $a++)
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
                ->setEndDate($faker->dateTimeBetween('-1 year', '-1 month'))
                ->setCoverImage('https://picsum.photos/seed/picsum/1000/350')
                ->setAuthor($users[rand(0, count($users)-1)])
                ->setArchived(FALSE);

            $movementsAssociated = $faker->randomElements($movements, $faker->numberBetween(1, 3));

            foreach ($movementsAssociated as $movement) {
                $artwork->addMovement($movement);
            }

            $artworks[] = $artwork;

            $manager->persist($artwork);
        }

        //auctions
        for ($b =1; $b <= 100; $b++){

            $auction = new Auction();

            $auction->setUser($users[rand(0, count($users)-1)])
                    ->setArtwork($artworks[rand(0, count($artworks)-1)])
                    ->setAmount($faker->randomFloat(2, 10000, 100000))
                    ->setSubmissionDate($faker->dateTimeBetween('-1 year', '-1 month'))
                    ->setSold('no');
            $manager->persist($auction);
        }


        for ($c=1; $c <=10 ; $c++){
            $contact = new Contact();
            $contact->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName())
                    ->setEmail($faker->email())
                    ->setMessage('<p>'.join('<p></p>',$faker->paragraphs(2)).'</p>');

            $manager->persist($contact);
        }


        $manager->flush();
    }
}
