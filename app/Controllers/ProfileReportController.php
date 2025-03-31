<?php
namespace Controllers;

use Core\Controller;
use Models\ProfileReport;
use Models\Profile;

class ProfileReportController extends Controller
{
    // Log file name for this controller's operations
    private $logFile = 'profile_report.log';
    
    // Instance of ProfileReport model for database operations
    private $profileReportModel;
    
    // Instance of Profile model for profile-related operations
    private $profileModel;

    public function __construct()
    {
        // Initialize parent Controller with logging
        parent::__construct($this->logFile);
        
        // Initialize models with the same log file
        $this->profileReportModel = new ProfileReport($this->logFile);
        $this->profileModel = new Profile($this->logFile);
    }

    /**
     * Handles profile reporting functionality
     */
    public function reportProfile()
    {
        // Authenticate and get current user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Check and process request content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Extract and validate required fields from input
        $profileId = $input['profileId'] ?? '';
        $reason = $input['reason'] ?? '';
        $details = $input['details'] ?? '';

        // Validate input data
        $profileId = intval($profileId); // Ensure profileId is an integer
        if (!$profileId || !$reason) {
            $this->sendBadRequest('Invalid profile ID or missing report reason');
        }

        try {
            // Attempt to create the profile report
            $report = $this->profileReportModel->createReport(
                $loggedInProfileId, 
                $profileId, 
                $reason, 
                $details
            );

            // Handle report creation response
            if ($report['success']) {
                // Success response
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile reported successfully.'
                ]);
            } else {
                // Report creation failed
                $this->sendInternalServerError($report['message'] ?? 'Failed to report profile');
            }
        } catch (\Exception $e) {
            // Log and handle any exceptions
            $this->logger->error(
                "Controllers/ProfileReportController->reportProfile(): " . 
                "Failed to report profile: " . $e->getMessage()
            );
            $this->sendInternalServerError();
        }
    }
}