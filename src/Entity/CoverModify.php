<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class CoverModify
{
    #[Assert\NotBlank(message:"Upload a file")]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/webp'], mimeTypesMessage:"Upload a jpg, jpeg, png or webp file")]
    #[Assert\File(maxSize:"4096k", maxSizeMessage: "This file is too big to be uploaded.")]
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