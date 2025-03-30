<?php
namespace Core;

/**
 * Base Controller class
 * 
 * All other controllers should extend this class
 */
class Controller
{
    /**
     * Session instance
     * @var Session
     */
    protected $session;
    
    /**
     * Logger instance
     * @var Logger
     */
    protected $logger;
    
    /**
     * Constructor
     */
    public function __construct($log_file = 'app.log')
    {
        // Initialize session and logger
        $this->session = new Session();
        $this->logger = new Logger($log_file);
    }
    
    /**
     * Check if user is authenticated
     * Redirect to login page if not
     * @param bool $redirect Whether to redirect to login page if not authenticated
     * @return bool True if authenticated, false otherwise
     */
    protected function requireAuth($redirect = true)
    {
        if (!$this->session->exists()) {
            if ($redirect) {
                $this->redirect('login');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Redirect to a specific URL
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect($url)
    {
        $fullUrl = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        
        header("Location: $fullUrl");
        exit;
    }

    protected function denyAccess()
    {
        $this->redirect('unauthorisedAccess');
        exit;
    }

    protected function extractInput($contentType){
        // Verify content type
        if (strpos($contentType, 'application/json') !== false) {
           return json_decode(file_get_contents('php://input'), true);
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            return $_POST; // Extract from $_POST
        } else {
            http_response_code(415); // Unsupported Media Type
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type.']);
            exit;
        }
    }

    protected function apiAuthLoggedInProfile(){
        $currentProfileId = $this->session->getProfileId();
        if (!$currentProfileId) {
          http_response_code(401); // Unauthorized
          header('Content-Type: application/json');
          echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
          ]);
          exit;
        }
        return $currentProfileId;
    }

    protected function enforceRequestMethod(string $expectedMethod){
        if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
            exit;
        }
    }

    protected function enforceContentType($contentType, $expectedContentType){
        if (strpos($contentType, $expectedContentType) === false) {
            http_response_code(415); // Unsupported Media Type
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type.']);
            exit;
        }
    }

    protected function sendInternalServerError($message = ''){
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message || 'Internal server error'
        ]);
        exit;
    }
}