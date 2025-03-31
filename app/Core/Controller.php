<?php
namespace Core;

/**
 * Base Controller class that provides common functionality for all controllers
 * 
 * Features include:
 * - Session management
 * - Request validation
 * - Authentication checks
 * - API response handling
 * - Content type enforcement
 * - Redirection utilities
 */
class Controller
{
    /**
     * Session handler instance for managing user sessions
     * @var Session
     */
    protected $session;
    
    /**
     * Logger instance for application logging
     * @var Logger
     */
    protected $logger;
    
    /**
     * Controller constructor
     * 
     * @param string $log_file Name of the log file (default: 'app.log')
     */
    public function __construct($log_file = 'app.log')
    {
        // Initialize session handler for user authentication
        $this->session = new Session();
        
        // Initialize logger for application events and errors
        $this->logger = new Logger($log_file);
    }
    
    /**
     * Verify user authentication status
     * 
     * @param bool $redirect Whether to automatically redirect unauthenticated users
     * @return bool True if authenticated, false otherwise
     */
    protected function requireAuth(bool $redirect = true): bool
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
     * Redirect to specified URL
     * 
     * @param string $url Relative or absolute URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        // Construct full URL from base URL and provided path
        $fullUrl = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        
        header("Location: $fullUrl");
        exit;
    }

    /**
     * Deny access by redirecting to unauthorized page
     * 
     * @return void
     */
    protected function denyAccess(): void
    {
        $this->redirect('unauthorisedAccess');
    }

    /**
     * Extract and parse input data based on content type
     * 
     * @param string $contentType The Content-Type header from request
     * @return array Parsed input data
     * @throws \RuntimeException On unsupported content type
     */
    protected function extractInput(string $contentType)
    {
        // Handle JSON input
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendBadRequest('Invalid JSON data');
            }
            return $input;
        } 
        // Handle form data
        elseif (strpos($contentType, 'multipart/form-data') !== false) {
            return $_POST;
        }
        
        // Unsupported content type
        $this->sendUnsupportedMediaType();
    }

    /**
     * Verify API authentication and return current profile ID
     * 
     * @return int Authenticated user's profile ID
     * @throws \RuntimeException If not authenticated
     */
    protected function apiAuthLoggedInProfile(): int
    {
        $currentProfileId = $this->session->getProfileId();
        if (!$currentProfileId) {
            $this->sendUnauthorized();
        }
        return $currentProfileId;
    }

    /**
     * Enforce specific HTTP request method
     * 
     * @param string $expectedMethod The required HTTP method (e.g., 'POST', 'GET')
     * @return void
     * @throws \RuntimeException On method mismatch
     */
    protected function enforceRequestMethod(string $expectedMethod): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($expectedMethod)) {
            $this->sendMethodNotAllowed();
        }
    }

    /**
     * Verify request content type matches expected type
     * 
     * @param string $contentType Actual content type from headers
     * @param string $expectedContentType Required content type
     * @return void
     * @throws \RuntimeException On content type mismatch
     */
    protected function enforceContentType(string $contentType, string $expectedContentType): void
    {
        if (strpos($contentType, $expectedContentType) === false) {
            $this->sendUnsupportedMediaType();
        }
    }

    /**
     * Send standardized 500 Internal Server Error response
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    protected function sendInternalServerError(string $message = 'Internal server error'): void
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send standardized 401 Unauthorized response
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    protected function sendForbidden(string $message = 'Forbidden'): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
    /**
     * Send standardized 401 Unauthorized response
     * 
     * @return void
     */
    protected function sendUnauthorized(): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }

    /**
     * Send standardized 405 Method Not Allowed response
     * 
     * @return void
     */
    protected function sendMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Method Not Allowed'
        ]);
        exit;
    }

    /**
     * Send standardized 415 Unsupported Media Type response
     * 
     * @return void
     */
    protected function sendUnsupportedMediaType(): void
    {
        http_response_code(415);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unsupported Content-Type'
        ]);
        exit;
    }

    /**
     * Send standardized 400 Bad Request response
     * 
     * @param string $message Optional custom error message
     * @return void
     */
    protected function sendBadRequest(string $message = 'Bad request'): void
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}