<?php
namespace App\DTOs;

use App\DTOs\ProfileDTO;
class PostDTO {
    public int $id;
    public ProfileDTO $profile;
    public string $date;
    public string $content;
    public array $images;
    public bool $likedByUser;
    public int $likes;
    public int $comments;
    public string $location;

    public function __construct(
        int $id,
        ProfileDTO $profile,
        string $date,
        string $content,
        array $images,
        bool $likedByUser,
        int $likes,
        int $comments,
        string $location = ''
    ) {
        $this->id = $id;
        $this->profile = $profile;
        $this->date = date('d/m/Y', strtotime($date));;
        $this->content = $content;
        $this->images = $images;
        $this->likedByUser = $likedByUser;
        $this->likes = $likes;
        $this->comments = $comments;
        $this->location = $location;
    }

    public function __toString(): string 
    {
        $imagesCount = count($this->images);
        $imagesInfo = $imagesCount > 0 ? 
            sprintf("%d images: [%s]", $imagesCount, implode(', ', array_map('basename', $this->images))) :
            "no images";
            
        return sprintf(
            "PostDTO [id: %d,\n\t\tprofile: {%s},\n\t\tdate: %s,\n\t\tcontent: %s,\n\t\timages: %s,\n\t\tlikedByUser: %s,\n\t\tlikes: %d, comments: %d,\n\t\tlocation: %s]",
            $this->id,
            $this->profile->__toString(),
            $this->date,
            mb_substr($this->content, 0, 50) . (mb_strlen($this->content) > 50 ? "..." : ""),
            $imagesInfo,
            $this->likedByUser ? "true" : "false",
            $this->likes,
            $this->comments,
            $this->location
        );
    }
}

?>