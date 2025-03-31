<?php
namespace Models;

use Core\Model;
use DTOs\ChatDTO;
use DTOs\ProfileDTO;
use DTOs\MessageDTO;
use Models\Profile;

class Chat extends Model
{
    private $logFile;
    public function __construct($logFile = 'chat.log')
    {
        parent::__construct($logFile);
        $this->logFile = $logFile;
    }
    
    /**
     * Get all chats for a profile
     * 
     * @param int $profileId The ID of the profile to get chats for
     * @return array Array of ChatDTO objects
     */
    public function getChatsForProfile(int $profileId): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get all chats the profile is part of
            $sql = "SELECT 
                        c.id as chat_id,
                        c.name as chat_name,
                        c.is_group_chat,
                        c.created_at,
                        pic.last_read_at,
                        m.content as last_message,
                        m.created_at as last_message_date,
                        m.sender_profile_id
                    FROM CHATS c
                    JOIN PROFILES_IN_CHAT pic ON c.id = pic.chat_id
                    LEFT JOIN (
                        SELECT 
                            chat_id, 
                            content, 
                            created_at, 
                            sender_profile_id,
                            ROW_NUMBER() OVER (PARTITION BY chat_id ORDER BY created_at DESC) as rn
                        FROM MESSAGES
                        WHERE is_deleted = FALSE
                    ) m ON m.chat_id = c.id AND m.rn = 1
                    WHERE pic.profile_id = :profileId
                    ORDER BY COALESCE(m.created_at, c.created_at) DESC";
            
            $this->db->query($sql);
            $this->db->bind(':profileId', $profileId);
            $chats = $this->db->resultSetAssoc();
            
            $chatDTOs = [];
            
            foreach ($chats as $chat) {
                // For non-group chats, get the other profile's information
                if (!$chat['is_group_chat']) {
                    $participants = $this->getChatParticipants($chat['chat_id']);
                    $profileDTO = $this->getOtherParticipant($participants, $profileId);
                } else {
                    // For group chats, create a placeholder profile with chat name
                    $profileDTO = new ProfileDTO(
                        0, // Placeholder ID
                        $chat['chat_name'],
                        'group_default.png', // Default group chat image
                        null,
                        null,
                        null
                    );
                }
                
                // Create ChatDTO
                $chatDTOs[] = new ChatDTO(
                    $chat['chat_id'],
                    $profileDTO,
                    $chat['last_message'] ?? 'No messages yet',
                    $chat['last_message_date'] ?? $chat['created_at'],
                    $chat['last_read_at'] ?? $chat['created_at']
                );
            }
            
            $this->db->commit();
            return $chatDTOs;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to get chats for profile: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieves messages for a specific chat with pagination support
     * 
     * This method fetches messages from the database for the given chat ID,
     * ordered by creation time. It caches participant profiles to improve performance
     * when constructing message DTOs. The method supports pagination through
     * limit and offset parameters.
     * 
     * @param int $chatId The ID of the chat to retrieve messages for
     * @param int $limit Maximum number of messages to return (default: 50)
     * @param int $offset Number of messages to skip for pagination (default: 0)
     * @return array An array of MessageDTO objects containing message data with sender profiles
     * @throws \Exception If database operations fail
     */
    public function getMessagesForChat(int $chatId, int $limit = 50, int $offset = 0): array
    {
        try {
            $this->db->beginTransaction();
            
            // First, get all profiles in this chat and cache them
            $profilesInChat = $this->getChatParticipants($chatId);
            
            // Get messages for the chat
            $sql = "SELECT 
                        id, 
                        sender_profile_id, 
                        content, 
                        created_at,
                        message_type
                    FROM MESSAGES 
                    WHERE chat_id = :chatId 
                    AND is_deleted = FALSE
                    ORDER BY created_at ASC
                    LIMIT :limit OFFSET :offset";
            
            $this->db->query($sql);
            $this->db->bind(':chatId', $chatId);
            $this->db->bind(':limit', $limit, \PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, \PDO::PARAM_INT);
            $messages = $this->db->resultSetAssoc();
            
            $messageDTOs = [];
            
            // Construct MessageDTOs using cached profile information
            foreach ($messages as $message) {
                $senderId = $message['sender_profile_id'];
                
                // Use cached profile if available, otherwise fetch it
                if (isset($profilesInChat[$senderId])) {
                    $senderProfile = $profilesInChat[$senderId];
                } else {
                    // This is a fallback in case a sender is somehow not in our cached list
                    $profileModel = new Profile($this->logFile);
                    $senderProfile = $profileModel->getProfileInfo($senderId);
                    // Cache it for potential future use
                    $profilesInChat[$senderId] = $senderProfile;
                }
                
                $messageDTOs[] = new MessageDTO(
                    $message['id'],
                    $senderProfile,
                    $message['content'],
                    $message['created_at']
                );
            }
            
            $this->db->commit();
            return $messageDTOs;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to get messages for chat: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all participants in a chat
     * 
     * @param int $chatId The ID of the chat
     * @param int|null $excludeProfileId Optional ID to exclude (for getting "other" participants)
     * @return array Array of ProfileDTO objects indexed by profile ID
     */
    public function getChatParticipants(int $chatId, ?int $excludeProfileId = null): array
    {
        try{
            $sql = "SELECT 
                        p.id,
                        p.full_name,
                        p.profile_picture
                    FROM PROFILES_IN_CHAT pic
                    JOIN PROFILES p ON pic.profile_id = p.id
                    WHERE pic.chat_id = :chatId";
            
            // Add condition to exclude a specific profile if needed
            if ($excludeProfileId !== null) {
                $sql .= " AND pic.profile_id != :excludeProfileId";
            }
            
            $this->db->query($sql);
            $this->db->bind(':chatId', $chatId);
            
            if ($excludeProfileId !== null) {
                $this->db->bind(':excludeProfileId', $excludeProfileId);
            }
            
            $participants = $this->db->resultSetObj();
            
            $profileDTOs = [];
            
            foreach ($participants as $participant) {
                $profileDTOs[$participant->id] = new ProfileDTO(
                    $participant->id,
                    $participant->full_name,
                    $participant->profile_picture
                );
            }
            
            // If we excluded a profile but need it for fallback, add it
            if (empty($profileDTOs) && $excludeProfileId !== null) {
                $profileModel = new Profile($this->logFile);
                $profileDTOs[$excludeProfileId] = $profileModel->getProfileInfo($excludeProfileId);
            }
            
            return $profileDTOs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get chat participants: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the other participant in a chat (for one-on-one chats)
     * 
     * @param array $chatParticipants array of chat participants
     * @param int $profileId The ID of the current profile
     * @return ProfileDTO The other participant's profile, or the current profile if no other participant exists
     */
    public function getOtherParticipant(array $chatParticipants, int $profileId): ProfileDTO
    {        
        // Filter to get only participants other than the current profile
        $otherParticipants = array_filter($chatParticipants, function($k) use ($profileId) {
            return $k != $profileId;
        }, ARRAY_FILTER_USE_KEY);
        
        // Return first other participant or fall back to current profile if none found
        return reset($otherParticipants) ?: $chatParticipants[$profileId] ?? (new Profile($this->logFile))->getProfileInfo($profileId);
    }

    /**
     * Check if a profile is a participant in a specific chat
     * 
     * @param int $chatId The ID of the chat
     * @param int $profileId The ID of the profile to check
     * @return bool True if the profile is a participant, false otherwise
     */
    public function isProfileInChat(int $chatId, int $profileId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count
                    FROM PROFILES_IN_CHAT
                    WHERE chat_id = :chatId
                    AND profile_id = :profileId";
            
            $this->db->query($sql);
            $this->db->bind(':chatId', $chatId);
            $this->db->bind(':profileId', $profileId);
            
            $result = $this->db->single();
            
            return intval($result->count) > 0;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to check if profile is in chat: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validates if a profile is a participant in a chat and returns the other participant if valid
     * @param int $chatId The ID of the chat
     * @param int $profileId The ID of the current profile
     * @return array An array with 'isParticipant' (boolean) and 'otherParticipant' (ProfileDTO or null)
     * This method doesn't strictly follow Single Responsibility Principle to avoid querying the databse twice
     */
    public function validateAndGetOtherParticipant(int $chatId, int $profileId): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get all participants in the chat using existing method
            $participants = $this->getChatParticipants($chatId);
            
            // Check if the current profile is a participant
            $isParticipant = isset($participants[$profileId]);
            
            // Find the other participant (if current profile is a participant)
            $otherParticipant = null;
            if ($isParticipant) {                
                $otherParticipant = $this->getOtherParticipant($participants, $profileId);
            }
            
            $this->db->commit();
            
            return [
                'isParticipant' => $isParticipant,
                'otherParticipant' => $otherParticipant
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to validate chat participation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save a new message after validating the sender is a participant in the chat
     * 
     * @param int $chatId The ID of the chat
     * @param int $profileId The ID of the sender profile
     * @param string $content The message content
     * @return MessageDTO|null The newly created message as DTO, or null if validation fails
     * @throws \Exception If database operation fails
     */
    public function saveMessage(int $chatId, int $profileId, string $content): ?MessageDTO
    {
        try {
            $this->db->beginTransaction();
            
            // First check if the profile is a participant in the chat
            if (!$this->isProfileInChat($chatId, $profileId)) {
                $this->logger->warning("Attempted to save message from non-participant. ChatID: $chatId, ProfileID: $profileId");
                $this->db->rollBack();
                return null;
            }
            
            // Insert the new message
            $sql = "INSERT INTO MESSAGES 
                    (chat_id, sender_profile_id, content, created_at, message_type, is_deleted) 
                    VALUES 
                    (:chatId, :profileId, :content, NOW(), 'text', FALSE)";
            
            $this->db->query($sql);
            $this->db->bind(':chatId', $chatId);
            $this->db->bind(':profileId', $profileId);
            $this->db->bind(':content', $content);
            $this->db->execute();
            
            // Get the ID of the newly created message
            $messageId = $this->db->getLastInsertId();
            
            // Update the last_read timestamp for the sender
            $this->updateLastReadTimestamp($chatId, $profileId);
            
            // Get profile information for creating the MessageDTO
            $profileModel = new Profile($this->logFile);
            $profileDTO = $profileModel->getProfileInfo($profileId);
            
            // Get the current timestamp for the created_at value
            $createdAt = date('Y-m-d H:i:s');
            
            // Create and return the MessageDTO
            $messageDTO = new MessageDTO(
                $messageId,
                $profileDTO,
                $content,
                $createdAt
            );
            
            $this->db->commit();
            return $messageDTO;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to save message: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the last read timestamp for a profile in a chat
     * 
     * @param int $chatId The ID of the chat
     * @param int $profileId The ID of the profile
     * @return bool True if successful, false if profile is not in chat
     * @throws \Exception If database operation fails
     */
    public function updateLastReadTimestamp(int $chatId, int $profileId): bool
    {
        try {
            // Check if the profile is in the chat
            if (!$this->isProfileInChat($chatId, $profileId)) {
                $this->logger->warning("Attempted to update last_read for non-participant. ChatID: $chatId, ProfileID: $profileId");
                return false;
            }
            
            $sql = "UPDATE PROFILES_IN_CHAT 
                    SET last_read_at = NOW() 
                    WHERE chat_id = :chatId 
                    AND profile_id = :profileId";
            
            $this->db->query($sql);
            $this->db->bind(':chatId', $chatId);
            $this->db->bind(':profileId', $profileId);
            $this->db->execute();
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update last read timestamp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get existing private chat ID between two profiles
     * 
     * @param int $profileId1 First profile ID
     * @param int $profileId2 Second profile ID
     * @return int|null Existing chat ID or null if no chat exists
     */
    public function getPrivateChatId(int $profileId1, int $profileId2): ?int
    {
        $this->logger->debug("Models/Chat->getPrivateChatId($profileId1, $profileId2)");
        try {
            $sql = "SELECT c.id 
                    FROM CHATS c
                    WHERE c.is_group_chat = FALSE 
                    AND c.id IN (
                        SELECT chat_id 
                        FROM PROFILES_IN_CHAT 
                        WHERE profile_id = :profileId1
                    )
                    AND c.id IN (
                        SELECT chat_id 
                        FROM PROFILES_IN_CHAT 
                        WHERE profile_id = :profileId2
                    );";

            $this->db->query($sql);
            $this->db->bind(':profileId1', $profileId1);
            $this->db->bind(':profileId2', $profileId2);

            $result = $this->db->single();
            $this->logger->debug("Models/Chat->getPrivateChatId(): result: " . print_r($result, true));

            return $result ? $result->id : null;
            
        } catch (\Exception $e) {
            $this->logger->error('Models/Chat->getPrivateChatId(): Failed to get private chat ID: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new private chat between two profiles
     * 
     * @param int $profileId1 First profile ID
     * @param int $profileId2 Second profile ID
     * @return int The ID of the newly created chat
     */
    public function createPrivateChat(int $profileId1, int $profileId2): int
    {
        try {
            $this->db->beginTransaction();
            
            // Create new chat record
            $sql = "INSERT INTO CHATS (is_group_chat, name) 
                    VALUES (FALSE, NULL)";
            
            $this->db->query($sql);
            $this->db->execute();
            $chatId = $this->db->getLastInsertId();
            
            // Add participants to PROFILES_IN_CHAT
            $participantSql = "INSERT INTO PROFILES_IN_CHAT (profile_id, chat_id) 
                               VALUES (:profileId1, :chatId), 
                                      (:profileId2, :chatId)";
            
            $this->db->query($participantSql);
            $this->db->bind(':profileId1', $profileId1);
            $this->db->bind(':profileId2', $profileId2);
            $this->db->bind(':chatId', $chatId);
            $this->db->execute();
            
            $this->db->commit();
            
            return intval($chatId);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to create private chat: ' . $e->getMessage());
            throw $e;
        }
    }
}