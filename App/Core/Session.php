<?php

namespace App\Core;

class Session {
    /**
     * Start session if not already started
     */
    private function ensureStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Create a user session
     * 
     * @param object $profileDTO User data to store in session
     */
    public function create($profileDTO) {
        $this->ensureStarted();
        //profileId
        $_SESSION['profileId'] = $profileDTO->id;
        //profilePicture
        $_SESSION['profilePicture'] = $profileDTO->profilePicture;
        $_SESSION['userId'] = $profileDTO->userId;
        $_SESSION['isAdmin'] = $profileDTO->isAdmin;
        $_SESSION['loginTime'] = time();
    }
    
    /**
     * Check if a user session exists
     * 
     * @return bool True if session exists
     */
    public function exists() {
        $this->ensureStarted();
        return isset($_SESSION['profileId']);
    }
    
    /**
     * Get profile ID from session
     * 
     * @return mixed Profile ID if exists, null otherwise
     */
    public function getProfileId() {
        $this->ensureStarted();
        return $_SESSION['profileId'] ?? null;
    }

    /**
     * Get if user is admin from session
     * 
     * @return bool isAdmin if exists, false otherwise
     */
    public function isAdmin() {
        $this->ensureStarted();
        return $_SESSION['isAdmin'] ?? false;
    }
    
    /**
     * Get all session data as array
     * 
     * @return array Session data
     */
    public function getAll() {
        $this->ensureStarted();
        return [
            //'email' => $_SESSION['userEmail'] ?? null,
            'profileId' => $_SESSION['profileId'] ?? null,
            'loginTime' => $_SESSION['loginTime'] ?? null
        ];
    }
    
    /**
     * Set a flash message in session
     * 
     * @param string $type Message type (success, error, info, etc.)
     * @param string $message The message text
     */
    public function setFlash($type, $message) {
        $this->ensureStarted();
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages and clear them
     * 
     * @return array Flash messages
     */
    public function getFlash() {
        $this->ensureStarted();
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Destroy the current session
     */
    public function destroy() {
        $this->ensureStarted();
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Regenerate session ID (for security)
     */
    public function regenerate() {
        $this->ensureStarted();
        //session_regenerateId();
    }
}