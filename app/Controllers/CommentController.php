<?php
namespace Controllers;

use Components\CommentComponent;
use Core\Controller;
use Models\Comment;
use DTOs\CommentDTO;

class CommentController extends Controller
{     
    private $logFile = 'comments.log';
    private $commentModel;
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->commentModel = new Comment($this->logFile);
    }
    public function getCommentHtml(CommentDTO $comment){
        // Capture the output of the include
        ob_start();
        // User data is available to these included files
        CommentComponent::renderComment($comment); // Template that includes profilePicture.php and profileName.php
        return ob_get_clean();
    }

    function getComments($postId) {
        $this->logger->debug("Controllers/CommentController->getComments($postId)");

        // Verify user is authenticated
        $profileId = $this->session->getProfileId() ?? null;
        
        if (!$profileId) {
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

        // Get format from query string
        $format = isset($_GET['format']) ? $_GET['format'] : 'json';

        // Query your database for comments
        $comments = $this->commentModel->getComments($postId);
        
        if ($format === 'html') {
            // Define html variable
            $html = '';
            
            // No comments returned
            if (empty($comments)) {
                $html = '<div class="alert alert-info">No comments.</div>';
            }

            // Iterate through comments and build html
            foreach ($comments as $comment) {
                $html .= $this->getCommentHtml($comment);
            }
            
            // Return html
            header('Content-Type: text/html');
            echo $html;
            exit;
        } else {
            // Return as JSON (default)
            header('Content-Type: application/json');
            echo json_encode($comments);
            exit;
        }
    }
    
    function createComment($postId) {
        $this->logger->debug("Controllers/CommentController->createComments($postId)");

        // Verify user is authenticated
        $profileId = $this->session->getProfileId() ?? null;
        
        if (!$profileId) {
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
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            $input = $_POST; // Extract from $_POST
        } else {
            http_response_code(415); // Unsupported Media Type
            echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type.']);
            exit;
        }
        // Get POST body data
        $content = $input['content'] ?? '';        
        
        if (!$content) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Get format from query string
        $format = $input['returnFormat'] ?? 'json';

        // Save comment to database and get the saved comment with ID
        $commentDto = $this->commentModel->saveCommentToDatabase($postId, $profileId, $content);
        
        if ($format === 'html'){
            $html = '';
            $html = $this->getCommentHtml($commentDto);
            // Return html
            http_response_code(201);
            header('Content-Type: text/html');
            echo $html;
            exit;
        }else{
            // Return as JSON (default)
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode($commentDto);
            exit;
        }
    }

}