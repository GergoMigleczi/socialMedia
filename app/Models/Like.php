<?php

namespace Models;

use Core\Model;

class Like extends Model{
    public function __construct($log_file = "like.log") {
        parent::__construct($log_file);
    }

    /**
     * Add a like to a post
     * 
     * @param int $postId The ID of the post to like
     * @param int $profileId The ID of the profile liking the post
     * @return bool True if successful, false otherwise
     */
    public function likePost(int $postId, int $profileId): bool {
        $this->logger->debug("Models/Like->likePost(): Attempting to like post: $postId by profile: $profileId");
        
        try {
            // Check if post exists
            if (!$this->postExists($postId)) {
                $this->logger->error("Models/Like->likePost(): Post $postId does not exist");
                return false;
            }
            
            // Insert the like (the UNIQUE constraint will prevent duplicates)
            $sql = "INSERT IGNORE INTO LIKES (post_id, profile_id) VALUES (:post_id, :profile_id)";
            
            $this->db->query($sql);
            $this->db->bind(':post_id', $postId);
            $this->db->bind(':profile_id', $profileId);
            
            $result = $this->db->execute();
            
            if ($result) {
                $this->logger->info("Models/Like->likePost(): Post $postId liked by profile $profileId");
                return true;
            } else {
                // This could happen if the like already exists (due to UNIQUE constraint)
                $this->logger->debug("Models/Like->likePost(): No changes made - like may already exist");
                return $this->isLikedByUser($postId, $profileId);
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Models/Like->likePost(): Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a like from a post
     * 
     * @param int $postId The ID of the post to unlike
     * @param int $profileId The ID of the profile unliking the post
     * @return bool True if successful, false otherwise
     */
    public function unlikePost(int $postId, int $profileId): bool {
        $this->logger->debug("Models/Like->unlikePost(): Attempting to unlike post: $postId by profile: $profileId");
        
        try {
            // Delete the like
            $sql = "DELETE FROM LIKES WHERE post_id = :post_id AND profile_id = :profile_id";
            
            $this->db->query($sql);
            $this->db->bind(':post_id', $postId);
            $this->db->bind(':profile_id', $profileId);
            
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->logger->info("Models/Like->unlikePost(): Post $postId unliked by profile $profileId");
            } else {
                $this->logger->debug("Models/Like->unlikePost(): No like found to delete");
            }
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error("Models/Like->unlikePost(): Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a post is liked by a specific user
     * 
     * @param int $postId The ID of the post
     * @param int $profileId The ID of the profile
     * @return bool True if liked, false otherwise
     */
    public function isLikedByUser(int $postId, int $profileId): bool {
        $sql = "SELECT COUNT(*) as count FROM LIKES WHERE post_id = :post_id AND profile_id = :profile_id";
        
        $this->db->query($sql);
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':profile_id', $profileId);
        
        $result = $this->db->single();
        
        return $result['count'] > 0;
    }

    /**
     * Get profiles who liked a post
     * 
     * @param int $postId The ID of the post
     * @param int $limit Optional limit for number of profiles to return
     * @param int $offset Optional offset for pagination
     * @return array Array of profiles who liked the post
     */
    public function getLikedByProfiles(int $postId, int $limit = 10, int $offset = 0): array {
        $sql = "
            SELECT 
                p.id,
                p.full_name,
                p.profile_picture
            FROM LIKES l
            JOIN PROFILES p ON l.profile_id = p.id
            WHERE l.post_id = :post_id
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $this->db->query($sql);
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':limit', $limit, \PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, \PDO::PARAM_INT);
        
        return $this->db->resultSetAssoc();
    }
    
    /**
     * Check if post exists
     * 
     * @param int $postId The ID of the post to check
     * @return bool True if exists, false otherwise
     */
    private function postExists(int $postId): bool {
        $sql = "SELECT id FROM POSTS WHERE id = :post_id AND is_deleted = FALSE";
        
        $this->db->query($sql);
        $this->db->bind(':post_id', $postId);
        
        $result = $this->db->single();
        
        return !empty($result);
    }
}