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
    public function showAddPost()
    {
        $this->requireAuth(true);
        
        $visibilityOptions = $this->postModel->getPostVisibilityOptions();
        // Render the login view
        View::render('pages/addPost', [
            'title' => 'Add Post',
            'visibilityOptions' => $visibilityOptions
        ]);
    }

    public function createPost()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        $profileId = $this->session->getProfileId() ?? null;
        if (!$profileId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'multipart/form-data') === false) {
            http_response_code(415); // Unsupported Media Type
            echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type.']);
            exit;
        }

        // get POST body data
        $content = $_POST['postContent'] ?? '';
        $visibility = $_POST['visibility'] ?? 'public';
        $locationName = $_POST['location'] ?? null;
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $isLocationVisible = true; 
        
        // Debugging $_POST data
        $this->logger->debug("Controllers/PostController->createPost(): POST Data: " . json_encode($_POST, JSON_PRETTY_PRINT));
        
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
            echo json_encode(['success' => true, 'message' => 'Post created successfully', 'post_id' => $postId]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create post']);
        }
    }
}