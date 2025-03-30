<?php
namespace Controllers;

use Core\Controller;
use Models\Like;

class LikeController extends Controller
{    
    private $logFile = 'likes.log';
    private $likeModel;  

    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->likeModel = new Like($this->logFile);
    }    
    /**
     * Create a like for a post
     * URI: /api/posts/{postId}/likes
     * Method: POST
     */
    public function createLike($postId) {
        $this->logger->debug("Controllers/LikeController->createLike($postId)");
        // Verify user is authenticated
        $profileId = $this->session->getProfileId() ?? null;
        
        if (!$profileId) {
            $this->logger->error("Conmessage: trollers/LikeController->createLike($postId): no profileId found in session");            
            http_response_code(401); // Unauthorized
            echo json_encode([
                'success' => false,
                'error' => 'Authentication required'
            ]);
            return;
        }
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'error' => 'Invalid post ID'
            ]);
            return;
        }
        
        // Attempt to create the like
        $result = $this->likeModel->likePost($postId, $profileId);
        
        if ($result) {
            // Get updated like count            
            http_response_code(201); // Created
            echo json_encode([
                'success' => true,
                'message' => 'Post liked successfully'
            ]);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'error' => 'Could not like post'
            ]);
        }
    }
    
    /**
     * Remove a like from a post
     * URI: /api/posts/{postId}/likes
     * Method: DELETE
     */
    public function deleteLike($postId) {
        $this->logger->debug("Controllers/LikeController->deleteLike($postId)");
        // Verify user is authenticated
        $profileId = $this->session->getProfileId() ?? null;
        
        if (!$profileId) {
            $this->logger->error("Controllers/LikeController->deleteLike($postId): no profileId found in session");            
            http_response_code(401); // Unauthorized
            echo json_encode([
                'success' => false,
                'error' => 'Authentication required'
            ]);
            return;
        }
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'error' => 'Invalid post ID'
            ]);
            return;
        }
        
        // Attempt to remove the like
        $result = $this->likeModel->unlikePost($postId, $profileId);
        if ($result) {            
            http_response_code(200); // OK (or could use 204 No Content)
            echo json_encode([
                'success' => true,
                'message' => 'Post unliked successfully'
            ]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode([
                'success' => false,
                'error' => 'Could not unlike post'
            ]);
        }
    }
}
?>