<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordModify
{

    #[Assert\NotBlank(message: "Please enter your old password")]
    private ?string $oldPassword = null;

    #[Assert\Length(min:6, max:255, minMessage: "Your password must be at least 6 characters long", maxMessage:"Your password should not be longer than 255 characters")]
    private ?string $newPassword = null;

    #[Assert\EqualTo(propertyPath:"newPassword", message:"Password not confirmed")]
    private ?string $confirmPassword = null;



    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): static
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): static
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
