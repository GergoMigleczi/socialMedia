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

    public static function formatTimestamp($timestamp): string {
        // Get the current timestamp for comparison
        $now = time();
        $messageTime = strtotime($timestamp);
        $diff = $now - $messageTime;
        
        // Calculate time differences
        $daysDiff = floor($diff / (60 * 60 * 24));
        $hoursDiff = floor($diff / (60 * 60));
        
        // Format based on how old the message is
        if ($daysDiff < 1 && date('Y-m-d', $now) === date('Y-m-d', $messageTime)) {
            // Today - show hours ago
            if ($hoursDiff < 1) {
                $minutesDiff = floor($diff / 60);
                return $minutesDiff <= 1 ? 'just now' : $minutesDiff . ' minutes ago';
            }
            return $hoursDiff <= 1 ? '1 hour ago' : $hoursDiff . ' hours ago';
        } elseif ($daysDiff < 4) {
            // In the last 3 days - show day name and time
            return date('l, H:i', $messageTime);
        } else {
            // Older than 3 days - show date and time
            return date('d/m/Y H:i', $messageTime);
        }
    }
}
?>