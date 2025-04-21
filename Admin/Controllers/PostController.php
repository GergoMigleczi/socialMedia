<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Post;

class PostController extends Controller
{   
    private $logFile = 'adminPosts.log';
    private $postModel;
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->postModel = new Post($this->logFile);
    }

    public function getPostStatistics(): void
    {
        $this->enforceRequestMethod('GET');
        $this->apiAuthLoggedInProfile();
        $this->apiAuthAdmin();

        // Extract input from get request
        if(isset($_GET['profileId'])) $profileId = intval($_GET['profileId']) ?? 0;
        if(isset($_GET['period'])) $period = $_GET['period'] ?? '';

        // Validate input
        if(!$profileId || !$period){
            $this->sendBadRequest('Missing arguments');
        }

        try{
            // Save post
            $postStatistics = $this->postModel->getPostStatistics($profileId, $period);

            if ($postStatistics) {
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'unit' => $postStatistics['unit'], 'postStatistics' => json_encode($postStatistics['dataset']), 'total' => $postStatistics['total']]);
                exit;
            } else {
                throw new \Exception();
            }
        }catch(\Exception $e){
            $this->logger->error("Admin/Controllers/PostController->getPostStatistics(): error: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}