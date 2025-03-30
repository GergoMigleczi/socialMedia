<?php

namespace Models;

use Core\Model;
use DTOs\ProfileDTO;

class Profile extends Model{
    public function __construct($log_file = "profile.log") {
        parent::__construct($log_file);
    }
    
    /**
     * Get profile information by profile ID
     * 
     * @param int $profileId The ID of the profile to retrieve
     * @return ProfileDTO|null ProfileDTO instance, or null if not found
     */
    public function getProfileInfo(int $profileId): ?ProfileDTO {
        $this->logger->debug("Models/Profile->getProfileInfo($profileId): Getting profile info for ID: $profileId");
        
        $sql = "
            SELECT 
                p.id,
                p.user_id,
                p.full_name,
                p.date_of_birth,
                p.profile_picture
            FROM PROFILES p
            WHERE p.id = :profile_id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':profile_id', $profileId);
        
        // Execute the query and get the result
        $this->db->execute();
        $result = $this->db->resultSetAssoc();
        
        // Check if profile was found
        if (empty($result)) {
            $this->logger->warning("Models/Profile->getProfileInfo($profileId): Profile not found");
            return null;
        }
        
        $profileData = $result[0];
        $this->logger->debug("Models/Profile->getProfileInfo($profileId): Profile found: " . $profileData['full_name']);
        
        // Create and return a ProfileDTO instance
        return new ProfileDTO(
            $profileData['id'],
            $profileData['full_name'],
            $profileData['profile_picture'],
            $profileData['user_id'],
            $profileData['date_of_birth']
        );
    }
    
    /**
     * Create a new profile
     * 
     * @param int $userId User ID
     * @param string $fullName Full name
     * @param string $dateOfBirth Date of birth (YYYY-MM-DD)
     * @param string $profilePicture Profile picture filename or path
     * @return ProfileDTO|null ProfileDTO of the created profile, or null if failed
     */
    public function createProfile(int $userId, string $fullName, string $dateOfBirth, string $profilePicture = 'default.png'): ?ProfileDTO {
        
        $sql = "
            INSERT INTO PROFILES (user_id, full_name, date_of_birth, profile_picture)
            VALUES (:user_id, :full_name, :date_of_birth, :profile_picture)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':full_name', $fullName);
        $this->db->bind(':date_of_birth', $dateOfBirth);
        $this->db->bind(':profile_picture', $profilePicture);
        
        if (!$this->db->execute()) {
            $this->logger->error("Models/Profile->createProfile($userId, $fullName): Failed to create profile");
            return null;
        }
        
        // Since we don't have direct access to lastInsertId(), we need to query it
        $profileId = $this->db->getLastInsertId();
        if (empty($result)) {
            $this->logger->error("Models/Profile->createProfile($userId, $fullName): Failed to get last insert ID after creating profile");
            return null;
        }
        
        $profileId = (int)$result[0]['id'];
        $this->logger->debug("Models/Profile->createProfile($userId, $fullName): Created new profile with ID: $profileId");
        
        // Return the new profile as a ProfileDTO
        return new ProfileDTO(
            $profileId,
            $fullName,
            $profilePicture,
            $userId,
            $dateOfBirth
        );
    }
    
    /**
     * Update an existing profile
     * 
     * @param int $profileId Profile ID to update
     * @param array $data Associative array of fields to update
     * @return ProfileDTO|null Updated ProfileDTO, or null if failed
     */
    public function updateProfile(int $profileId, array $data): ?ProfileDTO {
        $this->logger->debug("Models/Profile->updateProfile($profileId)");
        
        // Build the SET part of the query dynamically based on provided data
        $setParts = [];
        $allowedFields = ['full_name', 'date_of_birth', 'profile_picture'];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setParts[] = "$field = :$field";
            }
        }
        
        if (empty($setParts)) {
            $this->logger->warning("Models/Profile->updateProfile($profileId): No valid fields to update");
            return null;
        }
        
        $sql = "UPDATE PROFILES SET " . implode(', ', $setParts) . " WHERE id = :profile_id";
        
        $this->db->query($sql);
        $this->db->bind(':profile_id', $profileId);
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $this->db->bind(":$field", $value);
            }
        }
        
        if (!$this->db->execute()) {
            $this->logger->error("Models/Profile->updateProfile($profileId): Failed to update profile");
            return null;
        }
        
        $this->logger->debug("Models/Profile->updateProfile($profileId): Successfully updated");
        
        // Return the updated profile
        return $this->getProfileInfo($profileId);
    }
}