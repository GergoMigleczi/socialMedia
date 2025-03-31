<?php

namespace App\Models;

use App\Core\Model;
use App\DTOs\ProfileDTO;

class Friend extends Model{
    public function __construct($log_file = "friend.log") {
        parent::__construct($log_file);
    }
    
    /**
     * Get profile information by profile ID
     * 
     * @param int $profileId The ID of the profile to retrieve
     * @return array<ProfileDTO> |null 
     */
    public function getFriends(int $profileId) {
        $this->logger->debug("Models/Friends->getFriends($profileId)");
        try{
            $sql = "
            SELECT 
                p.id,
                p.user_id,
                p.full_name,
                p.date_of_birth,
                p.profile_picture
            FROM PROFILES p
            JOIN FRIENDSHIPS f ON 
                (f.profile_id_1 = p.id AND f.profile_id_2 = :profile_id) OR
                (f.profile_id_2 = p.id AND f.profile_id_1 = :profile_id)
            ";
            
            $this->db->query($sql);
            $this->db->bind(':profile_id', $profileId);
            
            // Execute the query and get the result
            $this->db->execute();
            $result = $this->db->resultSetAssoc();
            
            // Check if profile was found
            if (empty($result)) {
                $this->logger->debug("Models/Friends->getFriends($profileId): Friends not found");
                return [];
            }
            
            $friends = [];

            foreach ($result as $friend){
                $friends[] = new ProfileDTO(
                    $friend['id'],
                    $friend['full_name'],
                    $friend['profile_picture'],
                    $friend['user_id'],
                    $friend['date_of_birth']
                );
            }
            // Create and return a ProfileDTO instance
            return $friends; 
        }catch (\Exception $e) {
            $this->logger->error("Models/Friends->getFriends(): " . $e->getMessage());
            throw $e;
        }
    }   

    public function isFriend(int $profileId, int $friendProfileId): bool {
        $this->logger->debug("Models/Friends->isFriend($profileId, $friendProfileId)");
        try{
            $sql = "
            SELECT 
                'yes'
            FROM FRIENDSHIPS
            WHERE profile_id_1 = :smaller_id
            AND profile_id_2 = :larger_id
            ";
            
            $this->db->query($sql);
            $this->db->bind(':smaller_id',min($profileId, $friendProfileId));
            $this->db->bind(':larger_id',max($profileId, $friendProfileId));

            // Execute the query and get the result
            $this->db->execute();

            if ($this->db->rowCount() > 0){
                return true;
            }
            return false;
        }catch (\Exception $e) {
            $this->logger->error("Models/Friends->isFriend(): " . $e->getMessage());
            throw $e;
        }
        
    }   
    public function getFriendStatus(int $profileId, int $loggedInProfileId) {
        $this->logger->debug("Models/Friends->getFriendStatus($profileId, $loggedInProfileId)");
        try{
            if ($this->isFriend($profileId, $loggedInProfileId)){
                return 'Friends';
            }
            
            // Check for pending or existing friend requests
            $sql = "
                SELECT 
                    status,
                    sender_profile_id,
                    receiver_profile_id
                FROM FRIEND_REQUESTS
                WHERE (sender_profile_id = :sender_id AND receiver_profile_id = :receiver_id)
                OR (sender_profile_id = :receiver_id AND receiver_profile_id = :sender_id)
            ";
            
            $this->db->query($sql);
            $this->db->bind(':sender_id', $loggedInProfileId);
            $this->db->bind(':receiver_id', $profileId);
            $this->db->execute();
            
            $requestResult = $this->db->resultSetAssoc();
            
            if ($requestResult) {
                $requestResult = $requestResult[0];
                $this->logger->debug(json_encode($requestResult, JSON_PRETTY_PRINT));
                if ($requestResult['status'] === 'pending') {
                    // Determine if the logged-in user sent or received the request
                    if ($requestResult['sender_profile_id'] === $loggedInProfileId) {
                        return "Sent";
                    } else {
                        return "Received";
                    }
                }
            }
            return 'None';
        }catch (\Exception $e) {
            $this->logger->error("Models/Friends->getFriendStatus(): " . $e->getMessage());
            throw $e;
        }
    }

    public function sendFriendRequest(int $profileId, int $loggedInProfileId): bool {
        $this->logger->debug("Models/Friends->sendFriendRequest($profileId, $loggedInProfileId)");
        
        try {
            // Ensure user is not trying to send a request to themselves
            if ($profileId === $loggedInProfileId) {
                $this->logger->error("Cannot send friend request to yourself");
                return false;
            }
            
            // Check if a request already exists
            $checkSql = "
                SELECT id FROM FRIEND_REQUESTS 
                WHERE (sender_profile_id = :sender_id AND receiver_profile_id = :receiver_id)
            ";
            //OR (sender_profile_id = :receiver_id AND receiver_profile_id = :sender_id)
            
            $this->db->query($checkSql);
            $this->db->bind(':sender_id', $loggedInProfileId);
            $this->db->bind(':receiver_id', $profileId);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->logger->error("Friend request already exists");
                return false;
            }
            
            // Insert new friend request
            $sql = "
                INSERT INTO FRIEND_REQUESTS 
                (sender_profile_id, receiver_profile_id, status) 
                VALUES (:sender_id, :receiver_id, 'pending')
            ";
            
            $this->db->query($sql);
            $this->db->bind(':sender_id', $loggedInProfileId);
            $this->db->bind(':receiver_id', $profileId);
            
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error("Friend request error: " . $e->getMessage());
            return false;
        }
    }
    
    public function cancelFriendRequest(int $profileId, int $loggedInProfileId): bool {
        $this->logger->debug("Models/Friends->cancelFriendRequest($profileId, $loggedInProfileId)");
        
        try {
            $sql = "
                DELETE FROM FRIEND_REQUESTS 
                WHERE sender_profile_id = :sender_id 
                AND receiver_profile_id = :receiver_id 
                AND status = 'pending'
            ";
            
            $this->db->query($sql);
            $this->db->bind(':sender_id', $loggedInProfileId);
            $this->db->bind(':receiver_id', $profileId);
            
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error("Cancel friend request error: " . $e->getMessage());
            return false;
        }
    }
    
    public function acceptFriendRequest(int $profileId, int $loggedInProfileId): bool {
        $this->logger->debug("Models/Friends->acceptFriendRequest($profileId, $loggedInProfileId)");
        
        try {
            // Start a transaction to ensure data integrity
            $this->db->beginTransaction();
            
            // Update friend request status to accepted
            $updateSql = "
                DELETE FROM FRIEND_REQUESTS 
                WHERE sender_profile_id = :sender_id 
                AND receiver_profile_id = :receiver_id 
            ";
            
            $this->db->query($updateSql);
            $this->db->bind(':sender_id', $profileId);
            $this->db->bind(':receiver_id', $loggedInProfileId);
            $this->db->execute();
            $updateResult = $this->db->rowCount() > 0;
            
            // Check if update was successful
            if (!$updateResult) {
                $this->db->rollBack();
                return false;
            }
            
            // Insert into FRIENDSHIPS table
            $insertSql = "
                INSERT INTO FRIENDSHIPS 
                (profile_id_1, profile_id_2) 
                VALUES (:smaller_id, :larger_id)
            ";
            
            $this->db->query($insertSql);
            $this->db->bind(':smaller_id', min($profileId, $loggedInProfileId));
            $this->db->bind(':larger_id', max($profileId, $loggedInProfileId));
            $this->db->execute();
            $insertResult = $this->db->rowCount() > 0;
            
            // If insert fails, rollback
            if (!$insertResult) {
                $this->db->rollBack();
                return false;
            }
            
            // Commit the transaction
            $this->db->commit();
            
            return true;
        } catch (\Exception $e) {
            // Rollback in case of error
            $this->db->rollBack();
            $this->logger->error("Accept friend request error: " . $e->getMessage());
            return false;
        }
    }
    
    public function denyFriendRequest(int $profileId, int $loggedInProfileId): bool {
        $this->logger->debug("Models/Friends->denyFriendRequest($profileId, $loggedInProfileId)");
        
        try {
            $sql = "
                DELETE FROM FRIEND_REQUESTS 
                WHERE sender_profile_id = :sender_id 
                AND receiver_profile_id = :receiver_id 
            ";
            
            $this->db->query($sql);
            $this->db->bind(':sender_id', $profileId);
            $this->db->bind(':receiver_id', $loggedInProfileId);
            
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error("Deny friend request error: " . $e->getMessage());
            return false;
        }
    }
    
    public function unfriend(int $profileId, int $loggedInProfileId): bool {
        $this->logger->debug("Models/Friends->unfriend($profileId, $loggedInProfileId)");
        
        try {
            // Delete friendship record
            $sql = "
                DELETE FROM FRIENDSHIPS 
                WHERE profile_id_1 = :smaller_id 
                AND profile_id_2 = :larger_id
            ";
            
            $this->db->query($sql);
            $this->db->bind(':smaller_id', min($profileId, $loggedInProfileId));
            $this->db->bind(':larger_id', max($profileId, $loggedInProfileId));
            
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error("Unfriend error: " . $e->getMessage());
            return false;
        }
    }
}