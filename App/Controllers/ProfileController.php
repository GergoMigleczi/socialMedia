<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use Exception;
use App\Models\Friend;
use App\Models\Post;
use App\Models\Profile;
use App\Models\ProfileBlocking;
use App\Models\ProfileReport;

class ProfileController extends Controller
{        
    // Log file name for this controller's operations
    private $logFile = 'profile.log';
    
    // Model instances for database operations
    private $profileModel;        // Handles profile data
    private $postModel;           // Handles post data
    private $friendModel;         // Handles friend relationships
    private $profileBlockingModel; // Handles profile blocking
    private $profileReportModel;   // Handles profile reporting

    public function __construct()
    {
        // Initialize parent Controller with logging
        parent::__construct($this->logFile);
        
        // Initialize all required models
        $this->profileModel = new Profile($this->logFile);
        $this->postModel = new Post($this->logFile);
        $this->friendModel = new Friend($this->logFile);
        $this->profileBlockingModel = new ProfileBlocking($this->logFile);
        $this->profileReportModel = new ProfileReport($this->logFile);
    }  

    /**
     * Displays a user's profile page with their information and posts
     * @param int $profileId The ID of the profile to display
     */
    public function showProfile($profileId)
    {
        // Ensure user is authenticated (redirects to login if not)
        $this->requireAuth(true);

        // Get current logged-in user's profile ID from session
        $loggedInProfileId = $this->session->getProfileId();
        
        try {
            //Get basic profile information
            $profile = $this->profileModel->getProfileInfo($profileId);
            
            // Check if this is the user's own profile
            $isOwnProfile = $profileId == $loggedInProfileId;

            // Only check relationships if viewing someone else's profile
            if (!$isOwnProfile) {
                //Check friendship status between profiles
                $friendshipStatus = $this->friendModel->getFriendStatus($profileId, $loggedInProfileId);
                $this->logger->debug("Friendship status: " . $friendshipStatus);
                
                //Check blocking status in both directions
                $isBlockedByLoggedInProfile = $this->profileBlockingModel->isProfileBlocked($loggedInProfileId, $profileId);
                $isLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked($profileId, $loggedInProfileId);
            
                // Determine UI element visibility
                // Show chat button only if friends and neither has blocked the other
                $displayChatBtn = ($friendshipStatus == "Friends" && 
                                 !$isBlockedByLoggedInProfile && 
                                 !$isLoggedInProfileBlocked);
                
                // Show friend buttons only if neither has blocked the other
                $displayFriendBtn = (!$isBlockedByLoggedInProfile && 
                                    !$isLoggedInProfileBlocked);
            }

            // Get profile's posts (filtered for current viewer)
            $posts = $this->postModel->getProfilesPosts($profileId, $this->session->getProfileId());
            
            // Get available reporting options (for report dropdown)
            $reportOptions = $this->profileReportModel->getReportOptions();
            
            // Render the profile view with all collected data
            View::render('pages/profile', [
                'title' => 'Profile',
                'posts' => $posts,
                'profile' => $profile,
                'isOwnProfile' => $isOwnProfile,
                'friendshipStatus' => $friendshipStatus ?? '', // Default empty if own profile
                'displayFriendBtn' => $displayFriendBtn ?? false,
                'isBlockedByLoggedInProfile' => $isBlockedByLoggedInProfile ?? false,
                'isLoggedInProfileBlocked' => $isLoggedInProfileBlocked ?? false,
                'displayChatBtn' => $displayChatBtn ?? false,
                'reportOptions' => $reportOptions
            ]);
            
        } catch(Exception $e) {
            // Log any errors and show error page
            $this->logger->error("ProfileController->showProfile() failed: " . $e->getMessage());
            $this->redirect('500'); // Redirect to error page
        }
    }
}