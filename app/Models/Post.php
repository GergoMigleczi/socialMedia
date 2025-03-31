<?php

namespace Models;

use Core\Model;
use DTOs\PostDTO;
use DTOs\ProfileDTO;

class Post extends Model{
    public function __construct($log_file = "posts.log") {
        parent::__construct($log_file);
    }
    /**
     * Get all posts visible to a specific profile
     * 
     * @param int $profileId The ID of the profile viewing the posts
     * @return array Array of PostDTO objects
     */
    public function getVisiblePosts(int $profileId): array {        
        // Get all public posts and posts from friends that are visible to friends
        try{
            $sql = "
            SELECT 
                p.id as post_id,
                p.content,
                p.created_at,
                p.profile_id,
                p.location_name,
                p.latitude,
                p.longitude,
                prof.full_name,
                prof.profile_picture,
                (SELECT COUNT(*) FROM LIKES l WHERE l.post_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM COMMENTS c WHERE c.post_id = p.id AND c.is_deleted = FALSE) as comments_count,
                (SELECT COUNT(*) > 0 FROM LIKES l WHERE l.post_id = p.id AND l.profile_id = :profile_id) as liked_by_user
            FROM POSTS p
            JOIN PROFILES prof ON p.profile_id = prof.id
            LEFT JOIN FRIENDSHIPS f ON 
                (f.profile_id_1 = :profile_id AND f.profile_id_2 = p.profile_id) OR
                (f.profile_id_2 = :profile_id AND f.profile_id_1 = p.profile_id)
            WHERE 
                p.is_deleted = FALSE AND
                (
                    p.visibility = 'public' OR
                    (p.visibility = 'friends' AND f.id IS NOT NULL) OR
                    p.profile_id = :profile_id
                )
            ORDER BY p.created_at DESC
            ";
            
            $this->db->query($sql);
            $this->db->bind(':profile_id', $profileId);
            $results = $this->db->resultSetAssoc();
            
            $posts = [];
            
            foreach ($results as $row) {
                $this->logger->debug("Models/Post->getVisiblePosts($profileId): row: " . $row['post_id']);
                // Get images for this post
                $images = $this->getPostImages($row['post_id']);
                
                // Create ProfileDTO
                $profileDto = new ProfileDTO(
                    $row['profile_id'],
                    $row['full_name'],
                    $row['profile_picture']
                );
                
                // Create PostDTO
                $postDto = new PostDTO(
                    $row['post_id'],
                    $profileDto,
                    $row['created_at'],
                    $row['content'],
                    $images,
                    (bool)$row['liked_by_user'],
                    (int)$row['likes_count'],
                    (int)$row['comments_count'],
                    $row['location_name'] ?? ''
                );
                
                $posts[] = $postDto;
            }
            
            return $posts;
        }catch (\Exception $e) {
            $this->logger->error("Models/Post->getVisiblePosts(): Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProfilesPosts(int $profileId, int $requestingProfileId): array {        
        // Get all public posts and posts from friends that are visible to friends
        try{
            $sql = "
            SELECT 
                p.id as post_id,
                p.content,
                p.created_at,
                p.profile_id,
                p.location_name,
                p.latitude,
                p.longitude,
                prof.full_name,
                prof.profile_picture,
                (SELECT COUNT(*) FROM LIKES l WHERE l.post_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM COMMENTS c WHERE c.post_id = p.id AND c.is_deleted = FALSE) as comments_count,
                (SELECT COUNT(*) > 0 FROM LIKES l WHERE l.post_id = p.id AND l.profile_id = :profile_id) as liked_by_user
            FROM POSTS p
            JOIN PROFILES prof ON p.profile_id = prof.id
            LEFT JOIN FRIENDSHIPS f ON 
                (f.profile_id_1 = p.profile_id AND f.profile_id_2 = :requesting_profile_id) OR
                (f.profile_id_2 = p.profile_id AND f.profile_id_1 = :requesting_profile_id)
            WHERE 
                p.is_deleted = FALSE AND
                p.profile_id = :profile_id AND
                (
                    p.visibility = 'public' OR
                    (p.visibility = 'friends' AND (f.id IS NOT NULL OR :requesting_profile_id = :profile_id)) OR
                    (p.visibility = 'private' AND :requesting_profile_id = :profile_id)
                )
            ORDER BY p.created_at DESC
            ";
            
            $this->db->query($sql);
            $this->db->bind(':profile_id', $profileId);
            $this->db->bind(':requesting_profile_id', $requestingProfileId);

            $results = $this->db->resultSetAssoc();
            
            $posts = [];
            
            foreach ($results as $row) {
                $this->logger->debug("Models/Post->getProfilesPosts($profileId, $requestingProfileId): row: " . $row['post_id']);
                // Get images for this post
                $images = $this->getPostImages($row['post_id']);
                
                // Create ProfileDTO
                $profileDto = new ProfileDTO(
                    $row['profile_id'],
                    $row['full_name'],
                    $row['profile_picture']
                );
                
                // Create PostDTO
                $postDto = new PostDTO(
                    $row['post_id'],
                    $profileDto,
                    $row['created_at'],
                    $row['content'],
                    $images,
                    (bool)$row['liked_by_user'],
                    (int)$row['likes_count'],
                    (int)$row['comments_count'],
                    $row['location_name'] ?? ''
                );
                
                $posts[] = $postDto;
            }
            
            return $posts;
        }catch (\Exception $e) {
            $this->logger->error("Models/Post->getProfilesPosts(): Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all images for a specific post
     * 
     * @param int $postId The ID of the post
     * @return array Array of image URLs
     */
    private function getPostImages(int $postId): array {
        try{
            $sql = "
            SELECT media_url
            FROM POST_MEDIA
            WHERE post_id = :post_id AND media_type = 'image'
            ORDER BY position ASC
            ";
            
            $this->db->query($sql);
            $this->db->bind(':post_id', $postId);
            $results = $this->db->resultSetAssoc();
            
            $images = [];
            foreach ($results as $row) {
                $images[] = $row['media_url'];
            }
            
            return $images;
        }catch (\Exception $e) {
            $this->logger->error("Models/Post->getVisiblePosts(): Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all available visibility options for posts
     * @return array Array of available visibility options
     */
    public function getPostVisibilityOptions(): array {
        try{
            $sql = "
            SELECT SUBSTRING(COLUMN_TYPE, 6, LENGTH(COLUMN_TYPE) - 6) AS enum_values
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'POSTS'
            AND COLUMN_NAME = 'visibility'
            ";
            
            $this->db->query($sql);
            $result = $this->db->single();
            
            if (!$result) {
                $this->logger->error("Models/Post->getPostVisibilityOptions(): Failed to retrieve visibility options");
                return [];
            }
            
            // The result will be in format: 'public','friends','private'
            $enumString = $result->enum_values;
            
            // Remove the quotes and split by comma
            $options = array_map(function($value) {
                return trim($value, "'");
            }, explode(',', $enumString));
            
            $this->logger->debug("Models/Post->getPostVisibilityOptions(): Retrieved options: " . implode(', ', $options));
            
            return $options;
        }catch (\Exception $e) {
            $this->logger->error("Models/Post->getVisiblePosts(): Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new post with optional media files
     * 
     * @param int $profileId The ID of the profile creating the post
     * @param string $content The content of the post
     * @param string $visibility The visibility setting (public, friends, private)
     * @param array $mediaFiles Array of uploaded media files ($_FILES format)
     * @param string|null $locationName Optional location name
     * @param float|null $latitude Optional latitude coordinate
     * @param float|null $longitude Optional longitude coordinate
     * @param bool $isLocationVisible Whether the location should be visible
     * @return int|false The ID of the new post or false if creation failed
     */
    public function createPost(
        int $profileId, 
        string $content, 
        string $visibility = 'public', 
        array $mediaFiles = [], 
        ?string $locationName = null, 
        ?float $latitude = null, 
        ?float $longitude = null, 
        bool $isLocationVisible = true
    ): int|false {
        try {
            $this->logger->debug("Models/Post->createPost(): Starting post creation for profile $profileId");
            
            // Begin transaction to ensure all or nothing commits
            $this->db->beginTransaction();

            // Insert the post
            $sql = "
                INSERT INTO POSTS (
                    profile_id, 
                    content, 
                    visibility, 
                    location_name, 
                    latitude, 
                    longitude, 
                    is_location_visible
                ) VALUES (
                    :profile_id, 
                    :content, 
                    :visibility, 
                    :location_name, 
                    :latitude, 
                    :longitude, 
                    :is_location_visible
                )
            ";
            
            $this->db->query($sql);
            $this->db->bind(':profile_id', $profileId);
            $this->db->bind(':content', $content);
            $this->db->bind(':visibility', $visibility);
            $this->db->bind(':location_name', $locationName);
            $this->db->bind(':latitude', $latitude);
            $this->db->bind(':longitude', $longitude);
            $this->db->bind(':is_location_visible', $isLocationVisible);

            if (!$this->db->execute()) {
                $this->logger->error("Models/Post->createPost(): Failed to insert post");
                $this->db->rollBack();
                return false;
            }
            
            $postId = $this->db->getLastInsertId();
            $this->logger->debug("Models/Post->createPost(): Created post with ID: $postId");
            
            $this->logger->debug("Models/Post->createPost(): start saving media" . json_encode($mediaFiles, JSON_PRETTY_PRINT));
            // Process and save any media files
            if (!empty($mediaFiles) && isset($mediaFiles['media']) && !empty($mediaFiles['media']['name'][0])) {
                $mediaResult = $this->savePostMedia($postId, $mediaFiles);
                if (!$mediaResult) {
                    $this->logger->error("Models/Post->createPost(): Failed to save media for post $postId");
                    $this->db->rollBack();
                    return false;
                }
            }
            
            // Commit the transaction
            $this->db->commit();
            
            return intval($postId);
        } catch (\Exception $e) {
            $this->logger->error("Models/Post->createPost(): Exception: " . $e->getMessage());
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Save media files for a post
     * 
     * @param int $postId The ID of the post
     * @param array $files The uploaded files array ($_FILES)
     * @return bool True if successful, false otherwise
     */
    private function savePostMedia(int $postId, array $files): bool {
        $this->logger->debug("Models/Post->savePostMedia(): Processing media for post $postId");
        
        // Define allowed file types
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
        
        // Create upload directory path
        $postUploadDir = MEDIA_PATH . '/posts/';
        if (!file_exists($postUploadDir)) {
            mkdir($postUploadDir, 0755, true);
        }
        
        $position = 0;
        $totalFiles = count($files['media']['name']);
        
        try{
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($files['media']['error'][$i] !== UPLOAD_ERR_OK) {
                    $this->logger->error("Models/Post->savePostMedia(): Upload error for file {$i}: {$files['media']['error'][$i]}");
                    continue;
                }
                
                $tmpName = $files['media']['tmp_name'][$i];
                $fileType = $files['media']['type'][$i];
                $fileExtension = pathinfo($files['media']['name'][$i], PATHINFO_EXTENSION);
                
                // Determine media type
                $mediaType = '';
                if (in_array($fileType, $allowedImageTypes)) {
                    $mediaType = 'image';
                } elseif (in_array($fileType, $allowedVideoTypes)) {
                    $mediaType = 'video';
                } else {
                    $this->logger->error("Models/Post->savePostMedia(): Unsupported file type: $fileType");
                    continue;
                }
                
                $newFileName = $postId . '_' . $i . '.' . $fileExtension;
                $fullPath = $postUploadDir . $newFileName;
                
                // Move the uploaded file
                if (!move_uploaded_file($tmpName, $fullPath)) {
                    $this->logger->error("Models/Post->savePostMedia(): Failed to move uploaded file to $fullPath");
                    return false;
                }
                
                // Create relative URL for database (for web access)
                $relativeUrl = 'posts/' . $newFileName;
                
                // Create thumbnail for videos if needed
                $thumbnailUrl = null;
                if ($mediaType === 'video') {
                    // In a real implementation, you would generate a thumbnail
                    // This would be implemented based on your video processing tools
                }
                
                
                // Insert media record
                $sql = "
                    INSERT INTO POST_MEDIA (
                        post_id, 
                        media_type, 
                        media_url, 
                        thumbnail_url, 
                        position, 
                        alt_text
                    ) VALUES (
                        :post_id, 
                        :media_type, 
                        :media_url, 
                        :thumbnail_url, 
                        :position, 
                        :alt_text
                    )
                ";
                
                $this->db->query($sql);
                $this->db->bind(':post_id', $postId);
                $this->db->bind(':media_type', $mediaType);
                $this->db->bind(':media_url', $relativeUrl);
                $this->db->bind(':thumbnail_url', $thumbnailUrl);
                $this->db->bind(':position', $position);
                $this->db->bind(':alt_text', 'Media ' . ($position + 1)); // Generic alt text
                
                if (!$this->db->execute()) {
                    $this->logger->error("Models/Post->savePostMedia(): Failed to insert media record");
                    return false;
                }
                
                $position++;
                $this->logger->debug("Models/Post->savePostMedia(): Saved $mediaType file with GUID: $newFileName");
            }
            
            return true;
        }catch (\Exception $e) {
            $this->logger->error("Models/Post->getVisiblePosts(): Error: " . $e->getMessage());
            throw $e;
        }
    }
}