<?php
namespace App\DTOs;

class ProfileDTO
{
    public int $id;
    public string $fullName;
    public string $profilePicture;
    public ?string $email;
    public ?int $userId = null;
    public ?string $dateOfBirth = null;
    public ?bool $isAdmin = false;
    public function __construct(
        int $id, 
        string $fullName, 
        string $profilePicture,
        ?int $userId = null,
        ?string $dateOfBirth = null,
        ?string $email = null,
        ?bool $isAdmin = false
        ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->profilePicture = $profilePicture;
        $this->userId = $userId;
        $this->dateOfBirth = null;
        if ($dateOfBirth != null && $dateOfBirth != ''){
            $this->dateOfBirth = date('d/m/Y', strtotime($dateOfBirth));
        }
        $this->email = $email;
        $this->isAdmin = $isAdmin;
    }

    public function __toString(): string 
    {
        return sprintf(
            "ProfileDTO [\n" .
            "  id: %d,\n" .
            "  fullName: %s,\n" .
            "  profilePicture: %s,\n" .
            "  userId: %s,\n" .
            "  dateOfBirth: %s\n" .
            "  email: %s\n" .
            "  isAdmin: %s\n" .
            "]",
            $this->id,
            $this->fullName,
            $this->profilePicture,
            $this->userId ?? "null",
            $this->dateOfBirth ?? "null",
            $this->email ?? "null",
            $this->isAdmin ? 'True' : 'False'
        );
    }
}

?>