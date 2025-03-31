<?php
namespace App\DTOs;

use App\DTOs\ProfileDTO;
class MessageDTO {
    public int $id;
    public ProfileDTO $senderProfile;
    public string $content;
    public string $timestamp;

    public function __construct(
        int $id,
        ProfileDTO $senderProfile,
        string $content,
        string $timestamp    )
    {
        $this->id = $id;
        $this->senderProfile = $senderProfile;
        $this->timestamp = $this->formatTimestamp($timestamp);
        $this->content = $content;
    }

    public function __toString(): string 
    {
        return sprintf(
            "MessageDTO [id: %d,\n\t\tsender profile: {%s},\n\t\tcontent: %s,\n\t\ttimestamp: %s]",
            $this->id,
            $this->senderProfile->__toString(),
            $this->content,
            $this->timestamp
        );
    }

    private function formatTimestamp($timestamp): string{
        // Get the current timestamp for comparison
        $now = time();
        $messageTime = strtotime($timestamp);
        $daysDiff = floor(($now - $messageTime) / (60 * 60 * 24));

        // Format based on how old the message is
        if ($daysDiff < 1 && date('d/m/Y', $now) === date('d/m/Y', $messageTime)) {
            // Today - just show time
            return date('H:i', $messageTime);
        } elseif ($daysDiff < 4) {
            // In the last 3 days - show day name and time
            return date('l, H:i', $messageTime);
        } else {
            // Older than 3 days - keep the original format
            return date('d/m/Y H:i', $messageTime);
        }
    }
}
?>