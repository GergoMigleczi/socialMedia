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
        $this->apiAuthLoggedInProfile();
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            $this->sendBadRequest('Invalid profile ID');
        }

        try{
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
                http_response_code(200);
                header('Content-Type: text/html');
                echo $html;
                exit;
            } else {
                // Return as JSON (default)
                http_response_code( 200);
                header('Content-Type: application/json');
                echo json_encode($comments);
                exit;
            }
        } catch (\Exception $e) {
            $this->logger->error('Controllers/CommentController->getComments():' . $e->getMessage());
            $this->sendInternalServerError();
        }
        
    }
    
    function createComment($postId) {
        $this->logger->debug("Controllers/CommentController->createComments($postId)");

        $this->enforceRequestMethod('POST');

        // Verify user is authenticated
        $profileId = $this->apiAuthLoggedInProfile();
        
        // Validate post ID
        $postId = intval($postId);
        if (!$postId) {
            $this->sendBadRequest('Invalid profile ID');
        }
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);
        
        // Get POST body data
        $content = $input['content'] ?? '';        
        
        if (!$content) {
            $this->sendBadRequest('Content of the comment cannot be empty');
        }
        
        try{
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
        }catch (\Exception $e) {
            $this->logger->error('Controllers/CommentController->createComment():' . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}