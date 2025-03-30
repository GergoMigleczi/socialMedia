<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Exception;
use Models\User;

class AuthController extends Controller
{  
    private $logFile = 'authentication.log';
    private $userModel;  
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->userModel = new User($this->logFile);
    }

    /**
     * Display the login page
    */
    public function showLogin()
    {
        // If user is already logged in, redirect to home page
        if ($this->session->exists()) {
            // User is logged in
            $this->redirect('home');
        }
        
        // Get the current request URI
        $currentPath = trim($_SERVER['REQUEST_URI'], '/');

        // Define the login path
        $loginPath = 'socialMedia/public/login';

        // If not already on the login page, redirect
        if ($currentPath !== $loginPath) {
            $this->redirect('login');  // Stop execution after redirect
        }

        // Render the login view
        View::render('pages/login',
        [
            'title' => 'Login'
        ],
        false);
    }

    public function showRegister()
    {        
        // Render the register view
        View::render('pages/register',
        [
            'title' => 'Register'
        ],
        false);
    }

    public function showUnAuth()
    {        
        // Render the register view
        View::render('errors/unAuthAccess',
        [
            'title' => 'Unauthorised Access'
        ],
        false);
    }

    public function login()
    {        
        // Get input from JSON request body
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        $this->logger->debug("Controllers/AuthController->login(): Login attempt: " . $email . " password: " . $password);
        
        // Validate input
        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing credentials"]);
            exit;
        }
        
        // Attempt authentication
        $profileDTO = $this->userModel->login($email, $password);

        $this->logger->debug("Controllers/AuthController->login(): Login successful: " . $profileDTO->id);

        if ($profileDTO && isset($profileDTO->id)) {
            // Successful login
            
            // Create session
            $this->session->create($profileDTO);
            
            // Return success response with user data
            http_response_code(200);
            echo json_encode([
                "success" => true, 
                "message" => "Login successful"
            ]);
        } else {
            // Failed login
            $this->logger->debug("Controllers/AuthController->login(): Login failed: " . $email);
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid email or password"]);
        }
        exit;
    }

    public function register()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'multipart/form-data') === false) {
            http_response_code(415); // Unsupported Media Type
            echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type.']);
            exit;
        }

        // get POST body data
        $email = $_POST['email'];
        $password = $_POST['password'];
        $fullName = trim($_POST['firstName']) . ' ' . trim($_POST['lastName']);
        $dateOfBirth = $_POST['dateOfBirth'];
        if (isset($_FILES['profilePicture'])){
            $profilePicture = $_FILES['profilePicture'];
        };
        
        // Debugging $_POST data
        $this->logger->debug("Controllers/AuthController->register(): POST Data: " . json_encode($_POST, JSON_PRETTY_PRINT));
        $this->logger->debug("Controllers/AuthController->register(): FILES Data: " . json_encode($profilePicture, JSON_PRETTY_PRINT));

        // Save post
        try {
            $newUser = $this->userModel->createUser(
                $email, 
                $password, 
                $fullName, 
                $dateOfBirth,
                $profilePicture ?? null
            );
        }catch (Exception $e){
            $this->logger->error("Controllers/AuthController->register(): error: " . $e->getMessage());
        }
        
        if ($newUser && $newUser['success'] && isset($newUser['profileDTO'])) {
            // Create session
            $this->session->create($newUser['profileDTO']);
            echo json_encode(['success' => true, 'message' => 'User and Profile created successfully']);
        } else {
            if (isset($newUser['message'])){
                $message = $newUser['message'];
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $message ?? 'Failed to register']);
        }
    }

    public function logout(){
        if ($this->session->exists()) {
            // User is logged in
            $this->logger->debug("Controllers/AuthController->logout(): Terminate session for profileId: " . $this->session->getProfileId());
            $this->session->destroy();
        }
        $this->redirect('login');
    }
}