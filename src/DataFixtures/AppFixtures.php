<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Faq;
use App\Entity\User;
use App\Entity\Review;
use App\Entity\Artwork;
use App\Entity\Auction;
use App\Entity\Contact;
use App\Entity\Message;
use App\Entity\Movement;
use App\Entity\Conversation;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $passwordHasher;

    /**
     * Hash password
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

        //faq
        // for ($f =1; $f <= 5; $f++){
        //     $faq = new Faq();
        //     $faq->setQuestion($faker->sentence())
        //         ->setContent($faker->paragraph());
        //     $manager->persist($faq);
        // }

        //movements
        // $movements = [];
        // for ($m = 1; $m <= 10; $m++) {
        //     $movement = new Movement();
        //     $movement->setMovementName($faker->word());
        //     $manager->persist($movement);
        //     $movements[] = $movement;
        // }

        // admin
        $admin = new User();
        $admin->setFirstName('Kim')
            ->setLastName('Quenon')
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'))
            ->setEmail('admin@apollo.be')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'gallery'))
            ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(1)).'</p>')
            ->setRoles(['ROLE_ADMIN'])
            ->setPicture('');

        $manager->persist($admin);

        // experts
        $experts = [];
        for ($e = 1; $e <= 4; $e++) {
            $expert = new User();
            $hash = $this->passwordHasher->hashPassword($expert, 'password');

            $expert->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'))
                ->setEmail($faker->email())
                ->setPassword($hash)
                ->setDescription($faker->sentence())
                ->setRoles(['ROLE_EXPERT'])
                ->setPicture('');

            $manager->persist($expert);

            $experts[] = $expert;
        }

        $users = []; //array to stock users

        for($u = 1; $u <= 10; $u++)
        {
            $user = new User();
            $hash = $this->passwordHasher->hashPassword($user, 'password');

            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'))
                ->setEmail($faker->email())
                ->setPassword($hash)
                ->setDescription('<p>'.join('<p></p>',$faker->paragraphs(1)).'</p>')
                ->setPicture('');

                $manager->persist($user);

                $users[] = $user; //ajouter un user au tableau pour les annonces
        }



        //conversation & messages
        // for ($dm = 1; $dm <= 20; $dm++) {
        //     $conversation = new Conversation();
        
        //     $user = $users[rand(0, count($users) - 1)];
        //     $expert = $experts[rand(0, count($experts) - 1)];
        
        //     $conversation->setUser($user)
        //                  ->setExpert($expert);
        
        //     $manager->persist($conversation);
        
        
        //     for ($m = 1; $m <= 10; $m++) {
        //         //alterne entre l'user et l'expert 
        //         $sendBy = rand(0, 1) ? $user : $expert;

        //         $message = new Message();
        //         $message->setContent($faker->paragraph())
        //                 ->setSendBy($sendBy)
        //                 ->setTimestamp($faker->dateTimeBetween('-1 year', '-1 month'))
        //                 ->setConversation($conversation);
        
        //         $manager->persist($message);
        //     }
        // }
        



        // $artworks = [];
        // //artworks
        // for($a=1; $a<=30; $a++)
        // {
        //     $artwork = new Artwork();
        //     $media = ['Oil on canvas','Acrylic','Watercolor', 'Sketch', 'Gouache', 'Encaustic', 'Tempera', 'Pastel', 'Spray', 'Ink', 'Other'];

        //     $artwork->setTitle($faker->sentence())
        //         ->setArtistName($faker->lastName())
        //         ->setArtistSurname($faker->firstName())
        //         ->setYear($faker->year())
        //         ->setCanvaWidth($faker->randomFloat(2, 50, 1000))
        //         ->setCanvaHeight($faker->randomFloat(2, 50, 1000))
        //         ->setContent('<p>'.join('</p><p>', $faker->paragraphs(1)).'</p>')
        //         ->setMedium($media[array_rand($media)])
        //         ->setPriceInit($faker->randomFloat(2, 1000, 100000))
        //         ->setSubmissionDate($faker->dateTimeBetween('-1 year', '-1 month'))
        //         ->setEndDate($faker->dateTimeBetween('-1 year', '-1 month'))
        //         ->setCoverImage('https://picsum.photos/seed/picsum/1000/350')
        //         ->setAuthor($users[rand(0, count($users)-1)])
        //         ->setArchived(FALSE);

        //     $movementsAssociated = $faker->randomElements($movements, $faker->numberBetween(1, 3));

        //     foreach ($movementsAssociated as $movement) {
        //         $artwork->addMovement($movement);
        //     }

        //     //init tableau pour flush dans la table review
        //     $reviews = [];

        //     $review = new Review();
        //     $review->setAuthor($users[rand(0, count($users)-1)])
        //             ->setArtwork($artwork)
        //             ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month')) 
        //             ->setContent($faker->paragraph())
        //             ->setRating(rand(1,5));
        //     $manager->persist($review);
        //     $reviews[] = $review;
            
        //     $artworks[] = $artwork;

        //     $manager->persist($artwork);
        // }

        // $manager->flush();

        // // stocke dans table reviews
        // foreach ($reviews as $review) {
        //     $manager->persist($review);
        // }
        // $manager->flush();


        //auctions
        // for ($b =1; $b <= 100; $b++){

        //     $auction = new Auction();

        //     $auction->setUser($users[rand(0, count($users)-1)])
        //             ->setArtwork($artworks[rand(0, count($artworks)-1)])
        //             ->setAmount($faker->randomFloat(2, 10000, 100000))
        //             ->setSubmissionDate($faker->dateTimeBetween('-1 year', '-1 month'))
        //             ->setSold('no');
        //     $manager->persist($auction);
        // }

        //contact
        for ($c=1; $c <=10 ; $c++){
            $contact = new Contact();
            $contact->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName())
                    ->setEmail($faker->email())
                    ->setMessage('<p>'.join('<p></p>',$faker->paragraphs(2)).'</p>')
                    ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 month'));

            $manager->persist($contact);
        }


        $manager->flush();
    }
}
