<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class CoverModify
{
    #[Assert\NotBlank(message:"Veuillez ajouter un image")]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/webp'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png ou webp")]
    #[Assert\File(maxSize:"4096k", maxSizeMessage: "La taille du fichier est trop grande")]
    private $newPicture;

    public function getNewPicture(): ?string
    {
        return $this->newPicture;
    }

    public function setNewPicture(?string $newPicture): self
    {
        $this->newPicture = $newPicture;

        return $this;
    }
}