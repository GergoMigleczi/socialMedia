<?php
namespace Controllers;
use Components\FriendButtonComponent;
use Core\Controller;
use Core\View;
use Models\Friend;
use Models\ProfileBlocking;

class FriendController extends Controller
{    
    private $logFile = 'friend.log';
    private $friendModel;
    private $profileBlockingModel;
    public function __construct()
    {
        parent::__construct($this->logFile);
        $this->friendModel = new Friend($this->logFile);
        $this->profileBlockingModel = new ProfileBlocking($this->logFile);
    } 
    public function showFriends($profileId)
    {
        $this->requireAuth(true);

        if ($profileId != $this->session->getProfileId()){
            $this->denyAccess();
        }

        try{
            $friends = $this->friendModel->getFriends($profileId);
            // Render the login view
            View::render('pages/friends',
            [
                'title' => 'Friends',
                'friends' => $friends
            ]);
        }catch(\Exception $e){
            $this->logger->error("Controllers/FriendController->showFriends(): " . $e->getMessage());
            $this->redirect('500');
        }
        
    }

    public function handleFriendAction() {
        // Verify user is authenticated
        $loggedInProfileId = $this->apiAuthLoggedInProfile();
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        $action = $input['action'];
        $profileId = $input['profileId'];
        $this->logger->debug("Action: $action, profileId: $profileId, loggedInProfileId: $loggedInProfileId");
        try{
            //Is blocked by target profile
            if($this->profileBlockingModel->isProfileBlocked($profileId, $loggedInProfileId)){
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false,
                'message' => "Failed to $action friend request"]);
                exit;
            }

            switch($action) {
                case 'Send':
                    $response = $this->friendModel->sendFriendRequest($profileId, $loggedInProfileId);
                    //$this->notificationService->createFriendRequestNotification($profileId);
                    break;
                case 'Accept':
                    $response = $this->friendModel->acceptFriendRequest($profileId, $loggedInProfileId);
                    //$this->notificationService->createFriendAcceptedNotification($profileId);
                    break;
                case 'Deny':
                    $response = $this->friendModel->denyFriendRequest($profileId, $loggedInProfileId);
                    //$this->notificationService->createFriendDeniedNotification($profileId);
                    break;
                case 'Cancel':
                    $response = $this->friendModel->cancelFriendRequest($profileId, $loggedInProfileId);
                    // No notification created on cancellation, as specified
                    break;
                case 'Unfriend':
                    $response = $this->friendModel->unfriend($profileId, $loggedInProfileId);
                    // No notification created on cancellation, as specified
                    break;
            }

            if($response){
                $newStatus = $this->determineNewStatus($action);
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(['success' => true,
                    'friendButtons' => $this->getFriendButtonHtml($profileId, $newStatus),
                    'newStatus' => $newStatus
                    ]);
            }else{
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false,
                'message' => "Failed to $action friend request"]);
            }

        }catch (\Exception $e) {
            $this->logger->error('Controllers/FriendController->handleFriendAction():' . $e->getMessage());
            $this->sendInternalServerError();
        }
        
    }

    public function isFriend($profileId){
        $loggedInProfileId = $this->apiAuthLoggedInProfile();
        
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
            $isFriend = $this->friendModel->isFriend($loggedInProfileId, $profileId);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'isFriend' => $isFriend
            ]);
        }catch(\Exception $e) {
            $this->sendInternalServerError();
        }
    }

    private function determineNewStatus(string $action): string {
        switch ($action) {
            case 'Send':
                return 'Sent';
            case 'Accept':
                return 'Friends';
            case 'Deny':
            case 'Cancel':
            case 'Unfriend':
                return 'None';
        }
        return 'None';
    }

    public function getFriendButtonHtml(int $profileId, string $status, bool $display = true){
        // Capture the output of the include
        ob_start();
        
        FriendButtonComponent::render($profileId, $status, $display); 
        return ob_get_clean();
    }
}