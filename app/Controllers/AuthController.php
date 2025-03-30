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
    
    /**
     * AuthController constructor.
     * Initializes the logger and user model.
     */
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->userModel = new User($this->logFile);
    }

    /**
     * Display the login page.
     */
    public function showLogin()
    {
        // If user is already logged in, redirect to home page
        if ($this->session->exists()) {
            $this->redirect('home');
        }
        
        // Get the current request URI
        $currentPath = trim($_SERVER['REQUEST_URI'], '/');
        $loginPath = 'socialMedia/public/login';

        // Redirect if not already on the login page
        if ($currentPath !== $loginPath) {
            $this->redirect('login');
        }

        // Render the login view
        View::render('pages/login', ['title' => 'Login'], false);
    }

    /**
     * Display the registration page.
     */
    public function showRegister()
    {        
        View::render('pages/register', ['title' => 'Register'], false);
    }

    /**
     * Display unauthorized access page.
     */
    public function showUnAuth()
    {        
        View::render('errors/unAuthAccess', ['title' => 'Unauthorised Access'], false);
    }

    /**
     * Handle user login.
     * Extracts credentials from request, authenticates user, and starts session.
     */
    public function login()
    {        
        // Extract input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);
        
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        $this->logger->debug("Controllers/AuthController->login(): Login attempt: " . $email);
        
        // Validate input
        if (!$email || !$password) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Missing credentials"]);
            exit;
        }
        
        try{
            // Attempt authentication
            $profileDTO = $this->userModel->login($email, $password);

            if ($profileDTO && isset($profileDTO->id)) {
                // Successful login
                $this->logger->debug("Controllers/AuthController->login(): Login successful: " . $profileDTO->id);
                $this->session->create($profileDTO);
                
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["success" => true, "message" => "Login successful"]);
            } else {
                // Failed login
                $this->logger->error("Controllers/AuthController->login(): Login failed: " . $email);
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
            }
        }catch(Exception $e){
            $this->logger->error("Controllers/AuthController->login(): Failed to login: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * Handle user registration.
     * Extracts form data, creates a new user, and starts session if successful.
     */
    public function register()
    {        
        $this->enforceRequestMethod('POST');
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $this->enforceContentType($contentType, 'multipart/form-data');

        // Extract form data
        $email = $_POST['email'];
        $password = $_POST['password'];
        $fullName = trim($_POST['firstName']) . ' ' . trim($_POST['lastName']);
        $dateOfBirth = $_POST['dateOfBirth'];
        $profilePicture = $_FILES['profilePicture'] ?? null;
        
        // Debugging logs
        $this->logger->debug("Controllers/AuthController->register(): POST Data: " . json_encode($_POST, JSON_PRETTY_PRINT));
        $this->logger->debug("Controllers/AuthController->register(): FILES Data: " . json_encode($profilePicture, JSON_PRETTY_PRINT));

        try {
            // Create new user
            $newUser = $this->userModel->createUser(
                $email, 
                $password, 
                $fullName, 
                $dateOfBirth,
                $profilePicture
            );

            if ($newUser && $newUser['success'] && isset($newUser['profileDTO'])) {
                $this->session->create($newUser['profileDTO']);
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User and Profile created successfully']);
            } else {
                $message = $newUser['message'] ?? 'Failed to register';
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } catch (Exception $e) {
            $this->logger->error("Controllers/AuthController->register(): error: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * Handle user logout.
     * Terminates session and redirects to login page.
     */
    public function logout()
    {
        if ($this->session->exists()) {
            $this->logger->debug("Controllers/AuthController->logout(): Terminate session for profileId: " . $this->session->getProfileId());
            $this->session->destroy();
        }
        $this->redirect('login');
    }
}
