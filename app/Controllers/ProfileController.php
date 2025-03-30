<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Friend;
use Models\Post;
use Models\Profile;
use Models\ProfileBlocking;
use Models\ProfileReport;

class ProfileController extends Controller
{        
    private $logFile = 'profile.log';
    private $profileModel;
    private $postModel;
    private $friendModel;
    private $profileBlockingModel;
    private $profileReportModel;
    public function __construct()
    {
        parent::__construct($this->logFile);
        $this->profileModel = new Profile($this->logFile);
        $this->postModel = new Post($this->logFile);
        $this->friendModel = new Friend($this->logFile);
        $this->profileBlockingModel = new ProfileBlocking($this->logFile);
        $this->profileReportModel = new ProfileReport($this->logFile);
    }  

    public function showProfile($profileId)
    {
        $this->requireAuth(true);

        $loggedInProfileId = $this->session->getProfileId();
        // Get profile info
        $profile = $this->profileModel->getProfileInfo($profileId);
        $isOwnProfile = $profileId == $loggedInProfileId;

        if (!$isOwnProfile){
            $friendshipStatus = $this->friendModel->getFriendStatus($profileId, $loggedInProfileId);
            $this->logger->debug($friendshipStatus);
            $isBlockedByLoggedInProfile = $this->profileBlockingModel->isProfileBlocked($loggedInProfileId, $profileId);
            $isLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked($profileId, $loggedInProfileId);
        
            // Display chat button
            if ($friendshipStatus == "Friends" && !$isBlockedByLoggedInProfile && !$isLoggedInProfileBlocked) {
                $displayChatBtn = true;
            } else {
                $displayChatBtn = false;
            }
            // Display friend buttons
            if (!$isBlockedByLoggedInProfile && !$isLoggedInProfileBlocked) {
                $displayFriendBtn = true;
            } else {
                $displayFriendBtn = false;
            }
        }
        // Get Posts
        $posts = $this->postModel->getProfilesPosts($profileId, $this->session->getProfileId());
        $reportOptions = $this->profileReportModel->getReportOptions();
        
        // Render the login view
        View::render('pages/profile',
        [
            'title' => 'Profile',
            'posts' => $posts,
            'profile' => $profile,
            'isOwnProfile' => $isOwnProfile,
            'friendshipStatus' => $friendshipStatus ?? '',
            'displayFriendBtn' => $displayFriendBtn ?? false,
            'isBlockedByLoggedInProfile' => $isBlockedByLoggedInProfile ?? false,
            'isLoggedInProfileBlocked' => $isLoggedInProfileBlocked ?? false,
            'displayChatBtn' => $displayChatBtn ?? false,
            'reportOptions' => $reportOptions
        ]);
    }
}