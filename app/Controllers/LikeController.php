<?php
namespace Controllers;

use Core\Controller;
use ErrorException;
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
     */
    public function createLike($postId) {
        $this->logger->debug("Controllers/LikeController->createLike($postId)");
        $this->enforceRequestMethod("POST");
        // Verify user is authenticated
        $profileId = $this->apiAuthLoggedInProfile();
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            $this->sendBadRequest('Invalid profile ID');
        }
        
        try{
            // Attempt to create the like
            $result = $this->likeModel->likePost($postId, $profileId);
                    
            if ($result) {
                // Get updated like count            
                http_response_code(201); // Created
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Post liked successfully'
                ]);
            } else {
                throw new \Exception();
            }
        }catch (\Exception $e) {
            $this->logger->error('Controllers/LikeController->createLike():' . $e->getMessage());
            $this->sendInternalServerError();
        } 
    }
    
    /**
     * Remove a like from a post
     * URI: /api/posts/{postId}/likes
     * Method: DELETE
     */
    public function deleteLike($postId) {
        $this->logger->debug("Controllers/LikeController->deleteLike($postId)");
        $this->enforceRequestMethod("DELETE");

        // Verify user is authenticated
        $profileId = $this->apiAuthLoggedInProfile();
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            $this->sendBadRequest('Invalid profile ID');
        }
        
        try{
            // Attempt to remove the like
            $result = $this->likeModel->unlikePost($postId, $profileId);
            if ($result) {            
                http_response_code(200); // OK (or could use 204 No Content)
                echo json_encode([
                    'success' => true,
                    'message' => 'Post unliked successfully'
                ]);
            } else {
                throw new \Exception();
            }
        }catch (\Exception $e) {
            $this->logger->error('Controllers/LikeController->createLike():' . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}
?>