<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Post;

class PostController extends Controller
{   
    private $logFile = 'posts.log';
    private $postModel;
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->postModel = new Post($this->logFile);
    }   
    public function showAddPost(): void
    {
        $this->requireAuth(true);
        try{
            $visibilityOptions = $this->postModel->getPostVisibilityOptions();
            // Render the login view
            View::render('pages/addPost', [
                'title' => 'Add Post',
                'visibilityOptions' => $visibilityOptions
            ]);
        }catch(\Exception $e){
            $this->logger->error("Controllers/PostController->showAddPost(): " . $e->getMessage());
            $this->redirect('500');
        }
    }

    public function createPost(): void
    {
        $this->enforceRequestMethod('POST');

        $profileId = $this->apiAuthLoggedInProfile();
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $this->enforceContentType($contentType, 'multipart/form-data');
        
        // get POST body data
        $content = $_POST['postContent'] ?? '';
        $visibility = $_POST['visibility'] ?? 'public';
        $locationName = $_POST['location'] ?? null;
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $isLocationVisible = true; 
        
        // Debugging $_POST data
        $this->logger->debug("Controllers/PostController->createPost(): POST Data: " . json_encode($_POST, JSON_PRETTY_PRINT));
        
        try{
            // Save post
            $postId = $this->postModel->createPost(
                $profileId, 
                $content, 
                $visibility, 
                $_FILES, 
                $locationName, 
                $latitude ? (float) $latitude : null, 
                $longitude ? (float) $longitude : null, 
                $isLocationVisible
            );

            if ($postId) {
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Post created successfully', 'post_id' => $postId]);
                exit;
            } else {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create post']);
                exit;
            }
        }catch(\Exception $e){
            $this->logger->error("Controllers/PostController->createPost(): Failed to create post: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}