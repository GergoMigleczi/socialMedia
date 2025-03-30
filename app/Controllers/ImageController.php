<?php
namespace Controllers;

use Core\Controller;

class ImageController extends Controller
{    
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct('home.log');
    }
    public function getImage() {
        $this->enforceRequestMethod('GET');
        $this->apiAuthLoggedInProfile();
        
        $filename = $_GET['url'] ?? null;
        if (!$filename) {
            http_response_code(400);
            exit("Invalid request.");
        }
        
        $path = MEDIA_PATH . "/" . $filename;
        
        if (file_exists($path)) {
            $mimeType = mime_content_type($path);
            // Set Content-Type so front end can decode binary data of the image
            header('Content-Type: ' . $mimeType);
            // Output the binary data of the image directly
            readfile($path); 
            exit;
        } else {
            $this->logger->error("Controllers/ImageController->getImage($filename): image not found at $path");
            // Return 404 or default image
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
}