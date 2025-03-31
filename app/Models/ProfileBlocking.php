<?php

namespace App\Models;

use App\Core\Model;

class ProfileBlocking extends Model{
    public function __construct($logFile = "blocking.log") {
        parent::__construct($logFile);
    }

    /**
     * Block a profile
     * 
     * @param int $blockerProfileId ID of the profile doing the blocking
     * @param int $blockedProfileId ID of the profile being blocked
     * @return bool True if successful, false otherwise
     */
    public function blockProfile(int $blockerProfileId, int $blockedProfileId): array {
        $this->logger->debug("Models/BlockedProfile->blockProfile($blockerProfileId, $blockedProfileId): Blocking profile");
        
        // Check if profiles are the same
        if ($blockerProfileId === $blockedProfileId) {
            $this->logger->warning("Models/BlockedProfile->blockProfile: Cannot block own profile");
            return [
                'success' => false,
                'message' => 'Cannot block own profile'
            ];
        }
        
        try{
            // Check if already blocked
            if ($this->isProfileBlocked($blockerProfileId, $blockedProfileId)) {
                $this->logger->info("Models/BlockedProfile->blockProfile($blockerProfileId, $blockedProfileId): Profile already blocked");
                return [
                    'success' => true,
                    'message' => 'Profile was already blocked'
                ];// Already blocked, so operation is successful
            }
            
            $sql = "
                INSERT INTO BLOCKED_PROFILES (blocker_profile_id, blocked_profile_id)
                VALUES (:blocker_profile_id, :blocked_profile_id)
            ";
            
            $this->db->query($sql);
            $this->db->bind(':blocker_profile_id', $blockerProfileId);
            $this->db->bind(':blocked_profile_id', $blockedProfileId);
            
            if (!$this->db->execute()) {
                $this->logger->error("Models/BlockedProfile->blockProfile($blockerProfileId, $blockedProfileId): Failed to block profile");
                return [
                    'success' => false,
                    'message' => 'Failed to block profile'
                ];
            }
            
            $this->logger->debug("Models/BlockedProfile->blockProfile($blockerProfileId, $blockedProfileId): Successfully blocked profile");
            return [
                'success' => true,
                'message' => 'Profile blocked'
            ];
        }catch (\Exception $e) {
            $this->logger->error("Models/ProfileBlocking->blockProfile(): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Unblock a profile
     * 
     * @param int $blockerProfileId ID of the profile that did the blocking
     * @param int $blockedProfileId ID of the profile that was blocked
     * @return bool True if successful, false otherwise
     */
    public function unblockProfile(int $blockerProfileId, int $blockedProfileId): array {
        $this->logger->debug("Models/BlockedProfile->unblockProfile($blockerProfileId, $blockedProfileId): Unblocking profile");
        
        try{
            // Check if actually blocked
            if (!$this->isProfileBlocked($blockerProfileId, $blockedProfileId)) {
                $this->logger->info("Models/BlockedProfile->unblockProfile($blockerProfileId, $blockedProfileId): Profile not blocked");
                return [
                    'success' => true,
                    'message' => 'Profile was not blocked'
                ]; // Not blocked, so operation is successful
            }
            
            $sql = "
                DELETE FROM BLOCKED_PROFILES 
                WHERE blocker_profile_id = :blocker_profile_id 
                AND blocked_profile_id = :blocked_profile_id
            ";
            
            $this->db->query($sql);
            $this->db->bind(':blocker_profile_id', $blockerProfileId);
            $this->db->bind(':blocked_profile_id', $blockedProfileId);
            
            if (!$this->db->execute()) {
                $this->logger->error("Models/BlockedProfile->unblockProfile($blockerProfileId, $blockedProfileId): Failed to unblock profile");
                return [
                    'success' => false,
                    'message' => 'Failed to unblock profile'
                ];
            }
            
            $this->logger->debug("Models/BlockedProfile->unblockProfile($blockerProfileId, $blockedProfileId): Successfully unblocked profile");
            return [
                'success' => true,
                'message' => 'Profile unblocked'
            ];
        }catch (\Exception $e) {
            $this->logger->error("Models/ProfileBlocking->unblockProfile(): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if a profile is blocked by another profile
     * 
     * @param int $blockerProfileId ID of the profile that might be blocking
     * @param int $blockedProfileId ID of the profile that might be blocked
     * @return bool True if blocked, false otherwise
     */
    public function isProfileBlocked(int $blockerProfileId, int $blockedProfileId): bool {
        $this->logger->debug("Models/BlockedProfile->isProfileBlocked($blockerProfileId, $blockedProfileId): Checking if profile is blocked");
        try{
            $sql = "
            SELECT 1 
            FROM BLOCKED_PROFILES 
            WHERE blocker_profile_id = :blocker_profile_id 
            AND blocked_profile_id = :blocked_profile_id
            LIMIT 1
            ";
            
            $this->db->query($sql);
            $this->db->bind(':blocker_profile_id', $blockerProfileId);
            $this->db->bind(':blocked_profile_id', $blockedProfileId);
            
            $this->db->execute();
            $result = $this->db->resultSetAssoc();
            
            $isBlocked = !empty($result);
            $this->logger->debug("Models/BlockedProfile->isProfileBlocked($blockerProfileId, $blockedProfileId): Result: " . ($isBlocked ? "blocked" : "not blocked"));
            
            return $isBlocked;
        }catch (\Exception $e) {
            $this->logger->error("Models/ProfileBlocking->isProfileBlocked(): " . $e->getMessage());
            throw $e;
        }
    }
}