<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArtworkRepository;

#[ORM\Entity(repositoryClass: ArtworkRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Artwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $artistName = null;

    #[ORM\Column(length: 255)]
    private ?string $artistSurname = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?float $canvaWidth = null;

    #[ORM\Column]
    private ?float $canvaHeight = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $medium = null;

    #[ORM\Column]
    private ?float $priceInit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $submissionDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    private ?string $coverImage = null;

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
}
