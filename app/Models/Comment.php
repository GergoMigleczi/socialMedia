<?php
namespace Models;

use Core\Model;
use DTOs\CommentDTO;
use DTOs\ProfileDTO;
use Models\Profile;

class Comment extends Model{
    public function __construct($log_file = "comments.log") {
        parent::__construct($log_file);
    }
    /**
     * Get comments for a specific post
     * 
     * @param int $postId The ID of the post
     * @return array Array of CommentDTO objects
     */
    public function getComments(int $postId): array {
        $sql = "
            SELECT 
                c.id as comment_id,
                c.content,
                c.created_at,
                c.parent_comment_id,
                c.profile_id,
                prof.full_name,
                prof.profile_picture
            FROM COMMENTS c
            JOIN PROFILES prof ON c.profile_id = prof.id
            WHERE 
                c.post_id = :post_id AND 
                c.is_deleted = FALSE
            ORDER BY 
                c.parent_comment_id IS NULL DESC, 
                c.parent_comment_id ASC,
                c.created_at DESC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':post_id', $postId);
        $results = $this->db->resultSetAssoc();
        
        $comments = [];
        
        foreach ($results as $row) {
            $this->logger->debug("Models/Comment::getComments($postId): Processing comment id: " . $row['comment_id']);
                        
            // Create ProfileDTO for the comment author
            $profileDto = new ProfileDTO(
                $row['profile_id'],
                $row['full_name'],
                $row['profile_picture']
            );
            
            // Create CommentDTO
            $commentDto = new CommentDTO(
                $row['comment_id'],
                $profileDto,
                $row['created_at'],
                $row['content']
            );
            
            // Store in result array
            $comments[] = $commentDto;
        }
        
        return $comments;
    }

    /**
     * Save a new comment to the database
     * 
     * @param int $postId The ID of the post being commented on
     * @param int $profileId The ID of the profile making the comment
     * @param string $content The content of the comment
     * @param int|null $parentCommentId The parent comment ID if this is a reply (default: null)
     * @return CommentDTO|null The newly created comment as a DTO, or null if failed
     */
    public function saveCommentToDatabase(int $postId, int $profileId, string $content, ?int $parentCommentId = null): ?CommentDTO {
        $this->logger->debug("Saving comment: post_id=$postId, profile_id=$profileId");
        
        // Insert the comment into the database
        $sql = "
            INSERT INTO COMMENTS (post_id, profile_id, content, parent_comment_id) 
            VALUES (:post_id, :profile_id, :content, :parent_comment_id)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':profile_id', $profileId);
        $this->db->bind(':content', $content);
        $this->db->bind(':parent_comment_id', $parentCommentId);
        
        if (!$this->db->execute()) {
            $this->logger->error("Failed to save comment to database");
            return null;
        }
        
        // Get the ID of the newly inserted comment
        $commentId = $this->db->getLastInsertId();
        $this->logger->debug("New comment ID: $commentId");
        
        // Get profile information using the Profile model
        $profileModel = new Profile();
        $profileDto = $profileModel->getProfileInfo($profileId);
        
        if (!$profileDto) {
            $this->logger->error("Failed to get profile info for profile ID: $profileId");
            return null;
        }
        
        $commentDto = new CommentDTO(
            $commentId,
            $profileDto,
            date('Y-m-d'),
            $content
        );
        $this->logger->debug($commentDto->__toString());

        return $commentDto;
    }
}

?>