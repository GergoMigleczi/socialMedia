<?php
namespace App\DTOs;

use App\DTOs\ProfileDTO;

class CommentDTO {
    public int $id;
    public ProfileDTO $profile;
    public string $date;
    public string $content;

    public function __construct(
        int $id,
        ProfileDTO $profile,
        string $date,
        string $content
    ) {
        $this->id = $id;
        $this->profile = $profile;
        $this->date = date('d/m/Y', strtotime($date));
        $this->content = $content;
    }

    public function __toString(): string
    {
        return sprintf(
            "CommentDTO [" .
            "  id: %d,\n\t\t" .
            "  profile: {%s},\n\t\t" .
            "  date: %s,\n\t\t" .
            "  content: %s\n" .
            "]",
            $this->id,
            $this->profile->__toString(),  // Indent the nested user profile
            $this->date,
            $this->content
        );
    }
}

?>