<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Form\ArtworkType;
use App\Entity\CoverModify;
use App\Form\CoverModifyType;
use App\Service\PaginationService;
use App\Repository\ArtworkRepository;
use App\Repository\MovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ArtworkController extends AbstractController
{

    /**
     * Display all artworks
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @param MovementRepository $movementRepo
     * @return Response
     */
    #[Route('/artworks/{page<\d+>?1}', name: 'artworks_index')]
    public function index(PaginationService $pagination, int $page, MovementRepository $movementRepo): Response
    {

        $pagination->setEntityClass(Artwork::class)
        ->setPage($page)
        ->setLimit(9);

        //display all movements (filters)
        $movements = $movementRepo->findAll();
        //get actual dateTime (compare with artwork.endDate)
        $currentDate = new \DateTime();

        return $this->render('artworks/index.html.twig', [
            'pagination' => $pagination,
            'movements' => $movements,
            'currentDate'=> $currentDate
        ]);
    }

    /**
     * Search bar
     *
     * @param Request $request
     * @param ArtworkRepository $artworkRepo
     * @return JsonResponse
     */
    #[Route('/artworks/search/ajax', name: 'artworks_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, ArtworkRepository $artworkRepo): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (empty($query)) {
            return new JsonResponse([]); // empty array if 0 result found
        }

        //call to the function that determines which fields the search is based on
        $results = $artworkRepo->findByTitleOrArtistQuery($query)
            ->setMaxResults(10) //limit of results to display
            ->getQuery()
            ->getResult();

        //create array with fields required in twig
        $jsonResults = array_map(function ($artwork) {
            return [
                'title' => $artwork->getTitle(),
                'artist' => $artwork->getFullName(),
                'slug' => $artwork->getSlug(),
            ];
        }, $results);

        return new JsonResponse($jsonResults);
    }

    
    /**
     * Submit a new artwork
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/artworks/new", name:"artworks_create")]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $artwork = new Artwork();
        $form = $this->createform(ArtworkType::class, $artwork);

        //handle form + relation with movement table
        $form->handleRequest($request);

        //handle form
        if($form->isSubmitted() && $form->IsValid())
        {
            //handle cover
            $file = $form['coverImage']->getData();
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $artwork->setCoverImage($newFilename);

            }

            //ucword = capitalize each word
            $artwork->setTitle(ucwords($artwork->getTitle()));
            $artwork->setArtistName(ucwords($artwork->getArtistName()));
            $artwork->setArtistSurname(ucwords($artwork->getArtistSurname()));
            //set author with connected user
            $artwork->setAuthor($this->getUser());
            //boolean true : cannot archived a newly submitted artwork
            $artwork->setArchived(false);

            $manager->persist($artwork);
            
            //associate artwork to each of its movement
            foreach ($artwork->getMovements() as $movement)
            {
                $movement->addArtwork($artwork);
                $manager->persist($artwork);
            }
            
            $manager->flush();

            $this->addFlash(
                'success',
                "<strong>".$artwork->getTitle()."</strong> is ready to be sold ! Now wait and see..."
            );
        
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        return $this->render("artworks/new.html.twig",[
            'myForm' => $form->createView(),
            
        ]);
        
    }
    /**
     * Filter artworks by movement
     *
     * @param Movement $movement
     * @param ArtworkRepository $repo
     * @param MovementRepository $movementRepo
     * @return Response
     */
    #[Route("artworks/movements/{slug}", name: "movements_show")]
    public function showMovement(#[MapEntity(mapping: ['slug' => 'slug'])] Movement $movement, ArtworkRepository $repo, MovementRepository $movementRepo): Response
    {
        //get artworks from the movement
        $artworks = $movement->getArtwork();
        //get all movements
        $movements = $movementRepo->findAll();

        //display all the others movement except the one displayed
        $otherMovements = array_filter($movements, function ($m) use ($movement) {
            return $m->getSlug() !== $movement->getSlug();
        });

        //get actual datetime to compare with artwork.endDate
        $currentDate = new \DateTime();
        return $this->render('artworks/movements/show.html.twig', [
            'movement' => $movement,
            'otherMovements' => $otherMovements,
            'artworks' => $artworks,
            'currentDate' => $currentDate
        ]);

    }

    /**
     * Display single artwork
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/artworks/{slug}", name: "artworks_show")]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, ArtworkRepository $artworkRepo): Response
    {
        //display all movements associated with
        $movements = $artwork->getMovements();
        $author = $artwork->getAuthor();
        $currentDate = new \DateTime();

        //get all artworks of the same author
        $otherArtworks = $author->getArtworks()->toArray(); 
        //except the one displayed
        $otherArtworks = array_filter($otherArtworks, function ($otherArtwork) use ($artwork) {
            return $otherArtwork !== $artwork;
        });

        //filter on the archived artworks
        $archivedArtworks = $artworkRepo->findArchivedArtworksByUser($author);

        //get seller's rating
        $avgRating = $author->getAverageRating();

        return $this->render("artworks/show.html.twig", [
            'artwork' => $artwork,
            'movements' => $movements,
            'author' => $author,
            'currentDate' => $currentDate,
            'otherArtworks' => $otherArtworks,
            'archivedArtworks' => $archivedArtworks,
            'avgRating' => $avgRating,
            //context for _display.html.twig (delayed display)
            'context' => 'artworks_show',
        ]);
    }

    /**
     * Delete artwork
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("artworks/{slug}/delete", name:"artworks_delete")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER"))'),
        subject: new Expression('args["artwork"].getAuthor()'),
        message: "You are not allowed to delete someone else's artwork."
    )]
    public function deleteArtworks(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, EntityManagerInterface $manager): Response
    {       
            //delete cover
            unlink($this->getParameter('uploads_directory').'/'.$artwork->getCoverImage()); 
            $manager->remove($artwork);
            $manager->flush();

            $this->addFlash(
                'success',
                "<strong>".$artwork->getTitle()."</strong> deleted successfully !"
            );

        return $this->redirectToRoute('account_artworks');
    }

    /**
     * Edit artwork's cover
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("artworks/{slug}/cover-modify", name:"artworks_cover")]
    #[IsGranted('ROLE_USER')]
    public function coverModify(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager):Response
    {
        $coverModify = new CoverModify();
        $form = $this->createForm(CoverModifyType::class, $coverModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!empty($artwork->getCoverImage()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$artwork->getCoverImage());
            }

            //handle img
            $file = $form['newPicture']->getData();
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); //enlève les caractères spéciaux
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e){
                    return $e->getMessage();
                }
                $artwork->setCoverImage($newFilename);
            }
            $manager->persist($artwork);
            $manager->flush();


            $this->addFlash(
                'success',
                'Cover of '.$artwork->getTitle().' edited successfully'    
            );

            return $this->redirectToRoute('artworks_show',[
                'slug' => $artwork->getSlug()
              ]);
        }

        return $this->render("artworks/cover.html.twig",[
            'myForm'=>$form->createView(),
            'artwork'=>$artwork,
        ]);
    }

    /**
     * Edit artwork
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("artworks/{slug}/edit", name:"artworks_edit")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER"))'),
        subject: new Expression('args["artwork"].getAuthor()'),
        message: "You are not allowed to edit someone else's artwork."
        )]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        //cannot edit archived artwork
        if ($artwork->isArchived()) {
            $this->addFlash('danger', "You can't edit an archived artwork.");
            return $this->redirectToRoute('artworks_show', ['slug' => $artwork->getSlug()]);
        }

        //get img data
        $fileName = $artwork->getCoverImage();
        if(!empty($fileName)){
            $artwork->setCoverImage(
                new File($this->getParameter('uploads_directory').'/'.$artwork->getCoverImage())
            );
        }

        //boolean set to true to use ArtworkType edit version
        $form = $this->createForm(ArtworkType::class, $artwork, [
            'is_edit' => true
        ]);


        //handle form
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $artwork->setCoverImage($fileName);
            $manager->persist($artwork);
            
            foreach ($artwork->getMovements() as $movement)
            {
                $movement->addArtwork($artwork);
                $manager->persist($artwork);
            }

              $manager->flush();

              $this->addFlash(
                'success',
                "<strong>".$artwork->getTitle()."</strong> edited successfully !"
              );

              return $this->redirectToRoute('artworks_show',[
                'slug' => $artwork->getSlug()
              ]);

        }

        return $this->render("artworks/edit.html.twig",[
            "artwork"=> $artwork,
            "myForm"=> $form->createView()
        ]);
    }

}
