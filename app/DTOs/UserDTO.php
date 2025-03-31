<?php
namespace App\DTOs;

class UserDTO {
    public $id;
    public $email;
    public $profileId;
    
    public function __construct(int $id,
    string $email,
    int $profileId) {
        $this->id = $id;
        $this->email = $email;
        $this->profileId = $profileId;
    }
}
?>