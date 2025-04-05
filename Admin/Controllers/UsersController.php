<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\ProfileReport;
use App\Models\User;
use Exception;

class UsersController extends Controller
{    
    private $logFile = 'adminUsers.log';
    private $userModel;
    private $profileReportModel;
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->userModel = new User($this->logFile);
        $this->profileReportModel = new ProfileReport($this->logFile);
    }

    public function showUsers()
    {
        $this->requireAuth(true);
        $this->denyIfNotAdmin();

        $search = '';
        $sort = '';
        $sortDirection = 'ASC';
        $sortParamter = '';
        if(isset($_GET['search'])) $search = $_GET['search'];
        if(isset($_GET['sort'])){
            $sortParamter = $_GET['sort'];
            $sortPhrase = explode('-', $sortParamter);
            if(count($sortPhrase) > 0){
                $sort = $sortPhrase[0];
            }
            if(count($sortPhrase) > 1){
                $sortDirection = $sortPhrase[1];
            }
        }
        $this->logger->debug("Admin/Controllers/UsersController->showUsers($search, $sortParamter)");
        try{
            // Convert the sort parameter to the corresponding array value
            switch ($sort) {
                case 'name':
                    $sortField = 'full_name';
                    break;
                case 'posts':
                    $sortField = 'totalPosts';
                    break;
                case 'reports':
                    $sortField = 'totalReports';
                    break;
                case 'id':
                    $sortField = 'profile_id';
                    break;
                default:
                    // Default case as requested
                    $sortField = 'full_name';
                    break;
            }
            $profileDTOs = $this->userModel->getUsers($search, $sortField, $sortDirection);
            // Render the home view
            View::render('pages/users', [
                'title' => 'Users',
                'profileDTOs' => $profileDTOs,
                'search' => $search,
                'sort' => $sortParamter
            ], context: 'admin');
        }catch(\Exception $e){
            $this->logger->error("Controllers/Admin/HomeController->showUsers(): " . $e->getMessage());
            $this->redirect('500');
        }
    }

    public function showUser($userId)
    {
        $this->requireAuth(true);
        $this->denyIfNotAdmin();

        $this->logger->debug("Admin/Controllers/UsersController->showUser($userId)");
        try{
            $profileDTO = $this->userModel->getUserById($userId);
            $isBlocked = $this->userModel->isUserBlocked($userId);
            $reports = $this->profileReportModel->getReportsForProfile($profileDTO->id);
            // Render the home view
            View::render('pages/user', [
                'title' => 'Users',
                'profile' => $profileDTO,
                'reports' => $reports,
                'isBlocked' =>$isBlocked['isBlocked'],
                'blockedUntil' => $isBlocked['blockedUntil']
            ], context: 'admin');
        }catch(\Exception $e){
            $this->logger->error("Controllers/Admin/HomeController->showUsers(): " . $e->getMessage());
            $this->redirect('500');
        }
    }

    public function blockUser(){
        $this->enforceRequestMethod('POST');
        $this->apiAuthLoggedInProfile();
        $this->apiAuthAdmin();

        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        $userId = intval($input['userId']) ?? 0;
        $blockUnitDate = $input['blockedUntil'] ?? '';
        if(!$userId || !$blockUnitDate){
            $this->sendBadRequest('Missing required fields');
        }
        
        try{
            $userBlockResult = $this->userModel->blockUser($userId, $blockUnitDate);

            if($userBlockResult){
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }else{
                throw new \Exception('Failed to block user');
            }
        }catch(\Exception $e){
            $this->logger->error("Admin/Controllers/UsersController->blockUser($userId): error: " . $e->getMessage());
            $this->sendInternalServerError($e->getMessage());
        }
    }

    public function unblockUser(){
        $this->enforceRequestMethod('POST');
        $this->apiAuthLoggedInProfile();
        $this->apiAuthAdmin();

        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        $userId = intval($input['userId']) ?? 0;
        if(!$userId){
            $this->sendBadRequest('Missing required fields');
        }
        
        try{
            $userUnblockResult = $this->userModel->unblockUser($userId);

            if($userUnblockResult){
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }else{
                throw new \Exception('Failed to unblock user');
            }
        }catch(\Exception $e){
            $this->logger->error("Admin/Controllers/UsersController->unblockUser($userId): error: " . $e->getMessage());
            $this->sendInternalServerError($e->getMessage());
        }
    }

    public function deleteUser(int $userId){
        $this->enforceRequestMethod('DELETE');
        $this->apiAuthLoggedInProfile();
        $this->apiAuthAdmin();

        if(!$userId){
            $this->sendBadRequest('Missing user ID');
        }
        
        try{
            $deletionResult = $this->userModel->deleteUser($userId);

            if($deletionResult){
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }else{
                throw new \Exception('Failed to delete user');
            }
        }catch(\Exception $e){
            $this->logger->error("Admin/Controllers/UsersController->unblockUser($userId): error: " . $e->getMessage());
            $this->sendInternalServerError($e->getMessage());
        }
    }
}