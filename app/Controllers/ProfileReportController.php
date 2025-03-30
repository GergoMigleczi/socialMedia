<?php
namespace Controllers;

use Core\Controller;
use Models\ProfileReport;
use Models\Profile;

class ProfileReportController extends Controller
{
    private $logFile = 'profile_report.log';
    private $profileReportModel;
    private $profileModel;

    public function __construct()
    {
        parent::__construct($this->logFile);
        $this->profileReportModel = new ProfileReport($this->logFile);
        $this->profileModel = new Profile($this->logFile);
    }

    public function reportProfile()
    {
        // Get current logged-in user's profile ID
        $loggedInProfileId = $this->apiAuthLoggedInProfile();

        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get POST body data
        $profileId = $input['profileId'] ?? '';
        $reason = $input['reason'] ?? '';
        $details = $input['details'] ?? '';

        // Validate input
        $profileId = intval($profileId);
        if (!$profileId || !$reason) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid profile ID or missing reason'
            ]);
            exit;
        }

        try {
            // Report the profile
            $report = $this->profileReportModel->createReport($loggedInProfileId, $profileId, $reason, $details);

            if ($report['success']) {
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile reported successfully.'
                ]);
            } else {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $report['message'] ?? 'Failed to report profile'
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Controllers/ProfileReportController->reportProfile(): Failed to report profile: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
            exit;
        }
    }
}
