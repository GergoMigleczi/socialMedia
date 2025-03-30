<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Friend;
use Models\Post;
use Models\Profile;
use Models\ProfileBlocking;

class BlockingController extends Controller
{        
    private $logFile = 'blocking.log';
    private $profileBlockingModel;
    private $friendModel;
    public function __construct()
    {
        parent::__construct($this->logFile);
        $this->profileBlockingModel = new ProfileBlocking($this->logFile);
        $this->friendModel = new Friend($this->logFile);
    }  

    public function blockProfile(){
        // Get current logged-in user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get POST body data
        $profileId = $input['profileId'] ?? '';

        // Validate input
        $profileId = intval($profileId);
        if (!$profileId) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid profile ID'
            ]);
            exit;
        }

        try{
            $blocking = $this->profileBlockingModel->blockProfile($loggedInProfileId, $profileId);
            
            if($blocking['success']){
                try{
                    $this->friendModel->unfriend($profileId, $loggedInProfileId);
                    $this->friendModel->cancelFriendRequest( $profileId, $loggedInProfileId);
                    $status = $this->friendModel->getFriendStatus($profileId, $loggedInProfileId);
                }catch(\Exception $e){
                    $this->logger->error("Controllers/BlockingController->blockProfile(): Failed to unfriend profiles: " . $e->getMessage());
                }
                $friendController = new FriendController();
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'friendButton' => $friendController->getFriendButtonHtml($profileId, $status ?? '', false),
                    'message' => $blocking['message'] ?? ''
                ]);
            }else{
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $blocking['message'] ?? 'Failed to blockprofile'
                ]);
            }
        }catch(\Exception $e) {
            $this->logger->error("Controllers/BlockingController->blockProfile(): Failed to blockd profile: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
        }
    }

    public function unblockProfile(){
        // Get current logged-in user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get POST body data
        $profileId = $input['profileId'] ?? '';

        // Validate input
        $profileId = intval($profileId);
        if (!$profileId) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid profile ID'
            ]);
            exit;
        }

        try{
            $unblocking = $this->profileBlockingModel->unblockProfile($loggedInProfileId, $profileId);
            
            if($unblocking['success']){
                $isLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked($profileId, $loggedInProfileId);
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'isLoggedInProfileBlocked' => $isLoggedInProfileBlocked,
                    'message' => $blocking['message'] ?? ''
                ]);
            }else{
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $blocking['message'] ?? 'Failed to blockprofile'
                ]);
            }
        }catch(\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
}