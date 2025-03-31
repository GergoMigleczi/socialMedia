<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use Exception;
use App\Models\User;

/**
 * AuthController - Handles user authentication functionality
 * 
 * This controller manages all authentication-related actions including:
 * - Login and registration page display
 * - User authentication
 * - User registration with profile creation
 * - Session management
 * - Logout handling
 */
class AuthController extends Controller
{  
    /**
     * Path to the authentication log file
     * @var string
     */
    private $logFile = 'authentication.log';
    
    /**
     * Instance of the User model for database operations
     * @var User
     */
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
     * 
     * Renders the login view if user is not already logged in.
     * If user is already authenticated, redirects to home page.
     * Also ensures the correct URL path is being used.
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
        // This ensures consistent URL structure
        if ($currentPath !== $loginPath) {
            $this->redirect('login');
        }

        // Render the login view without the standard layout
        View::render('pages/login', ['title' => 'Login'], false);
    }

    /**
     * Display the registration page.
     * 
     * Renders the registration view for new users to create accounts.
     */
    public function showRegister()
    {        
        View::render('pages/register', ['title' => 'Register'], false);
    }

    /**
     * Display unauthorized access page.
     * 
     * Renders an error page when a user attempts to access
     * a resource without proper authorization.
     */
    public function showUnAuth()
    {        
        View::render('errors/unAuthAccess', ['title' => 'Unauthorised Access'], false);
    }

    /**
     * Handle user login.
     * 
     * Processes login requests by:
     * 1. Extracting email and password from the request
     * 2. Validating the input
     * 3. Authenticating via the User model
     * 4. Creating a new session on success
     * 5. Returning appropriate JSON response
     */
    public function login()
    {        
        // Extract input based on content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);
        
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        $this->logger->debug("Controllers/AuthController->login(): Login attempt: " . $email);
        
        // Validate input - both email and password must be provided
        if (!$email || !$password) {
            $this->sendBadRequest("Missing credentials");
        }
        
        try{
            // Attempt authentication through the user model
            $profileDTO = $this->userModel->login($email, $password);

            if ($profileDTO && isset($profileDTO->id)) {
                // Successful login - create session with profile data
                $this->logger->debug("Controllers/AuthController->login(): Login successful: " . $profileDTO->id);
                $this->session->create($profileDTO);
                
                // Return success response
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["success" => true, "message" => "Login successful"]);
            } else {
                // Failed login - invalid credentials
                $this->logger->debug("Controllers/AuthController->login(): Login failed: " . $email);
                $this->sendForbidden("Invalid email or password");
            }
        }catch(Exception $e){
            // Log any exceptions and return a generic error
            $this->logger->error("Controllers/AuthController->login(): Failed to login: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * Handle user registration.
     * 
     * Processes new user registration by:
     * 1. Enforcing POST method and multipart/form-data content type
     * 2. Extracting user details from form submission
     * 3. Creating a new user account with profile
     * 4. Creating a session on successful registration
     * 5. Returning appropriate JSON response
     */
    public function register()
    {        
        // Ensure this endpoint only accepts POST requests
        $this->enforceRequestMethod('POST');
        
        // Verify content type is multipart/form-data (needed for file uploads)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $this->enforceContentType($contentType, 'multipart/form-data');

        // Extract form data from POST and FILES superglobals
        $email = $_POST['email'];
        $password = $_POST['password'];
        $fullName = trim($_POST['firstName']) . ' ' . trim($_POST['lastName']);
        $dateOfBirth = $_POST['dateOfBirth'];
        $profilePicture = $_FILES['profilePicture'] ?? null;
        
        // Log the form data for debugging purposes
        $this->logger->debug("Controllers/AuthController->register(): POST Data: " . json_encode($_POST, JSON_PRETTY_PRINT));
        $this->logger->debug("Controllers/AuthController->register(): FILES Data: " . json_encode($profilePicture, JSON_PRETTY_PRINT));

        try {
            // Attempt to create a new user with the provided details
            $newUser = $this->userModel->createUser(
                $email, 
                $password, 
                $fullName, 
                $dateOfBirth,
                $profilePicture
            );

            if ($newUser && $newUser['success'] && isset($newUser['profileDTO'])) {
                // Registration successful - create session and return success response
                $this->session->create($newUser['profileDTO']);
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User and Profile created successfully']);
            } else {
                // Registration failed - return error with specific message
                $message = $newUser['message'] ?? 'Failed to register';
                $this->sendInternalServerError($message);
            }
        } catch (Exception $e) {
            // Log any exceptions and return a generic error
            $this->logger->error("Controllers/AuthController->register(): error: " . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * Handle user logout.
     * 
     * Terminates the current session if one exists and
     * redirects the user to the login page.
     */
    public function logout()
    {
        if ($this->session->exists()) {
            // Log the logout event with the profile ID
            $this->logger->debug("Controllers/AuthController->logout(): Terminate session for profileId: " . $this->session->getProfileId());
            $this->session->destroy();
        }
        // Redirect to login page after logout
        $this->redirect('login');
    }
}