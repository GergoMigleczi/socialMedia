<?php

namespace Controllers;

use Core\Controller;
use Models\Friend;
use Models\ProfileBlocking;

/**
 * BlockingController - Handles user profile blocking and unblocking functionality
 * 
 * This controller manages the API endpoints for blocking and unblocking user profiles
 * in the social platform. It interacts with ProfileBlocking and Friend models to
 * manage the relationships between users.
 */
class BlockingController extends Controller
{        
    /**
     * Path to the log file for blocking-related actions
     * @var string
     */
    private $logFile = 'blocking.log';
    
    /**
     * Instance of the ProfileBlocking model
     * @var ProfileBlocking
     */
    private $profileBlockingModel;
    
    /**
     * Instance of the Friend model
     * @var Friend
     */
    private $friendModel;
    
    /**
     * BlockingController constructor to initialize models
     * 
     * Sets up the parent controller with the log file and instantiates
     * the required models for friend and blocking operations.
     */
    public function __construct()
    {
        parent::__construct($this->logFile);
        $this->profileBlockingModel = new ProfileBlocking($this->logFile);
        $this->friendModel = new Friend($this->logFile);
    }

    /**
     * Block a profile
     * 
     * API endpoint that allows a user to block another user's profile.
     * When a user is blocked:
     * 1. They are removed from the blocker's friend list
     * 2. Any pending friend requests between users are canceled
     * 3. A blocking relationship is established in the database
     * 
     * @return void Outputs JSON response
     */
    public function blockProfile(){
        // Get current logged-in user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Verify content type and extract the input data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get profile ID to block from the request body
        $profileId = $input['profileId'] ?? '';

        // Validate the profile ID (must be a valid integer)
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
            // Attempt to block the profile using the model
            $blocking = $this->profileBlockingModel->blockProfile($loggedInProfileId, $profileId);
            
            if($blocking['success']){
                try{
                    // If blocking was successful, remove any friend relationships
                    // First, unfriend if they are already friends
                    $this->friendModel->unfriend($profileId, $loggedInProfileId);
                    // Second, cancel any pending friend requests
                    $this->friendModel->cancelFriendRequest($profileId, $loggedInProfileId);
                    // Get the updated friend status
                    $status = $this->friendModel->getFriendStatus($profileId, $loggedInProfileId);
                }catch(\Exception $e){
                    // Log any errors that occur during the unfriending process
                    $this->logger->error("Controllers/BlockingController->blockProfile(): Failed to unfriend profiles: " . $e->getMessage());
                }
                
                // Generate updated HTML for the friend button to reflect the new status
                $friendController = new FriendController();
                $friendButton = $friendController->getFriendButtonHtml($profileId, $status ?? '', false);
                
                // Return success response with the updated friend button HTML
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'friendButton' => $friendButton,
                    'message' => $blocking['message'] ?? ''
                ]);
            }else{
                // Return error response if blocking failed
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $blocking['message'] ?? 'Failed to block profile'
                ]);
            }
        }catch(\Exception $e) {
            // Log any uncaught exceptions and return a generic error
            $this->logger->error("Controllers/BlockingController->blockProfile(): Failed to block profile: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * Unblock a profile
     * 
     * API endpoint that allows a user to unblock a previously blocked profile.
     * This removes the blocking relationship but does not restore friend status.
     * After unblocking, the user will need to send a new friend request if desired.
     * 
     * @return void Outputs JSON response
     */
    public function unblockProfile(){
        // Get current logged-in user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Verify content type and extract the input data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get profile ID to unblock from the request body
        $profileId = $input['profileId'] ?? '';

        // Validate the profile ID (must be a valid integer)
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
            // Attempt to unblock the profile using the model
            $unblocking = $this->profileBlockingModel->unblockProfile($loggedInProfileId, $profileId);
            
            if($unblocking['success']){
                // Check if the logged-in profile is blocked by the other profile
                // This determines if the user can now interact with the unblocked profile
                $isLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked($profileId, $loggedInProfileId);
                
                // Return success response with the blocking status
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'isLoggedInProfileBlocked' => $isLoggedInProfileBlocked,
                    'message' => $unblocking['message'] ?? ''
                ]);
            }else{
                // Return error response if unblocking failed
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $unblocking['message'] ?? 'Failed to unblock profile'
                ]);
            }
        }catch(\Exception $e) {
            // Log any uncaught exceptions and return a generic error
            $this->logger->error("Controllers/BlockingController->unblockProfile(): Failed to unblock profile: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}