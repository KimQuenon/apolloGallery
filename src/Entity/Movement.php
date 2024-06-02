<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MovementRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: MovementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Movement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Artwork::class, inversedBy: 'movements')]
    private Collection $artwork;

    #[ORM\Column(length: 255)]
    private ?string $movementName = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->artwork = new ArrayCollection();
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
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->movementName);
        };
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Artwork>
     */
    public function getArtwork(): Collection
    {
        return $this->artwork;
    }

    public function addArtwork(Artwork $artwork): static
    {
        if (!$this->artwork->contains($artwork)) {
            $this->artwork->add($artwork);
        }

        return $this;
    }

    public function removeArtwork(Artwork $artwork): static
    {
        $this->artwork->removeElement($artwork);

        return $this;
    }

    public function getMovementName(): ?string
    {
        return $this->movementName;
    }

    public function setMovementName(string $movementName): static
    {
        $this->movementName = $movementName;

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
}
