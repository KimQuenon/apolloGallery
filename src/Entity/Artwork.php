<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArtworkRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtworkRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields:['slug'], message:"This url is already taken, try to modify the title of your artwork")]
class Artwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Length(min: 5, max:100, minMessage:"The title must be at least 5 characters long.", maxMessage: "The title can't be longer than 100 characters.")]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max:50, minMessage:"The name of the artist must be at least 2 characters long.", maxMessage: "The name of the artist can't be longer than 50 characters.")]
    private ?string $artistName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max:50, minMessage:"The surname of the artist must at least 2 characters long.", maxMessage: "The surname of the artist can't be longer than 50 characters.")]
    private ?string $artistSurname = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $year = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?float $canvaWidth = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?float $canvaHeight = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min: 20, minMessage:"The description must be at least 20 characters long.")]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Select a type of medium.")]
    private ?string $medium = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Type in your price.")]
    private ?float $priceInit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $submissionDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/webp'], mimeTypesMessage:"Upload a jpg, jpeg, png or webp file")]
    #[Assert\File(maxSize:"4096k", maxSizeMessage: "This file is too large to be uploaded.")]
    private ?string $coverImage = null;

    #[ORM\ManyToMany(targetEntity: Movement::class, mappedBy: 'artwork')]
    #[Assert\Count(
        min: 1,
        minMessage: 'Select at least one movement.'
    )]
    private Collection $movements;

    #[ORM\ManyToOne(inversedBy: 'artworks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(targetEntity: Auction::class, mappedBy: 'artwork', orphanRemoval: true)]
    private Collection $auctions;

    #[ORM\Column]
    private ?bool $archived = null;

    #[ORM\OneToOne(mappedBy: 'artwork', cascade: ['persist', 'remove'])]
    private ?Review $review = null;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
        $this->auctions = new ArrayCollection();
    }

    /**
     * init slug
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug(): void
    {
        if(empty($this->slug))
        {
            $slugify = new Slugify();
            //slugify the artist name and the artwork's title
            $artistSurnameSlug = $slugify->slugify($this->artistSurname);
            $artistNameSlug = $slugify->slugify($this->artistName);
            $titleSlug = $slugify->slugify($this->title);

            $this->slug = $artistSurnameSlug . '-' . $artistNameSlug . '-' . $titleSlug;
        }
    }

    /**
     * add current datetime
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if(empty($this->submissionDate))
        {
            $this->submissionDate = new \DateTime();
        }
    }

    /**
     * function to get full name of the artist
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->artistSurname." ".$this->artistName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getArtistName(): ?string
    {
        return $this->artistName;
    }

    public function setArtistName(string $artistName): static
    {
        $this->artistName = $artistName;

        return $this;
    }

    public function getArtistSurname(): ?string
    {
        return $this->artistSurname;
    }

    public function setArtistSurname(string $artistSurname): static
    {
        $this->artistSurname = $artistSurname;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getCanvaWidth(): ?float
    {
        return $this->canvaWidth;
    }

    public function setCanvaWidth(float $canvaWidth): static
    {
        $this->canvaWidth = $canvaWidth;

        return $this;
    }

    public function getCanvaHeight(): ?float
    {
        return $this->canvaHeight;
    }

    public function setCanvaHeight(float $canvaHeight): static
    {
        $this->canvaHeight = $canvaHeight;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getMedium(): ?string
    {
        return $this->medium;
    }

    public function setMedium(string $medium): static
    {
        $this->medium = $medium;

        return $this;
    }

    public function getPriceInit(): ?float
    {
        return $this->priceInit;
    }

    public function setPriceInit(float $priceInit): static
    {
        $this->priceInit = $priceInit;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(\DateTimeInterface $submissionDate): static
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    /**
     * @return Collection<int, Movement>
     */
    public function getMovements(): Collection
    {
        return $this->movements;
    }

    public function addMovement(Movement $movement): static
    {
        if (!$this->movements->contains($movement)) {
            $this->movements->add($movement);
            $movement->addArtwork($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): static
    {
        if ($this->movements->removeElement($movement)) {
            $movement->removeArtwork($this);
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Auction>
     */
    public function getAuctions(): Collection
    {
        return $this->auctions;
    }

    public function addAuction(Auction $auction): static
    {
        if (!$this->auctions->contains($auction)) {
            $this->auctions->add($auction);
            $auction->setArtwork($this);
        }

        return $this;
    }

    public function removeAuction(Auction $auction): static
    {
        if ($this->auctions->removeElement($auction)) {
            // set the owning side to null (unless already changed)
            if ($auction->getArtwork() === $this) {
                $auction->setArtwork(null);
            }
        }

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(Review $review): static
    {
        // set the owning side of the relation if necessary
        if ($review->getArtwork() !== $this) {
            $review->setArtwork($this);
        }

        $this->review = $review;

        return $this;
    }
}
