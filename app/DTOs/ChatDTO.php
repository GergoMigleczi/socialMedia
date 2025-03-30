<?php
namespace DTOs;

use DTOs\ProfileDTO;
class ChatDTO {
    public int $id;
    public ProfileDTO $profile;
    public string $lastMessageDate;
    public string $lastMessage;
    public string $lastReadDate;

    public function __construct(
        int $id,
        ProfileDTO $profile,
        string $lastMessage,
        string $lastMessageDate,
        string $lastReadDate
    ) {
        $this->id = $id;
        $this->profile = $profile;
        $this->lastMessageDate = date('d/m/Y', strtotime($lastMessageDate));
        $this->lastMessage = $lastMessage;
        $this->lastReadDate = date('d/m/Y', strtotime($lastReadDate));
    }

    public function __toString(): string 
    {
            
        return sprintf(
            "ChatDTO [id: %d,\n\t\tprofile: {%s},\n\t\tlast message: %s,\n\t\tlast message date: %s,\n\t\tlast read date: %s]",
            $this->id,
            $this->profile->__toString(),
            $this->lastMessage,
            $this->lastMessageDate,
            $this->lastReadDate
        );
    }
}
?>