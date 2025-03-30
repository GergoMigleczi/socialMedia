<?php

namespace Models;

use Core\Model;
use DTOs\ProfileDTO;


class User extends Model{
    // User properties
    public $id;
    public $email;
    private $password;
    public $createdAt;
    
    // User profile properties (from PROFILES table)
    public $profileId;
    public $fullName;
    public $dateOfBirth;
    public $profile_picture;

    public function __construct($logFile = "authentication.log") {
        parent::__construct($logFile);
    }
    /**
     * Register a new user
     * 
     * @param array $data User data including email and password
     * @return bool Success or failure
     */
    public function register($data) {
        // Prepare query to insert new user
        $this->db->query("INSERT INTO USERS (email, password) VALUES (:email, :password)");
        
        // Hash password before storing
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Bind values
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $hashedPassword);
        
        // Execute query
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Authenticate a user for login
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return mixed User object if authenticated, false otherwise
     */
    public function login($email, $password): bool|ProfileDTO {
        $this->logger->debug("Models/User->login($email, $password)");
        try{
            // Find user by email
            $this->db->query("SELECT u.*, p.id as profile_id, p.full_name, p.date_of_birth, p.profile_picture 
            FROM USERS u
            LEFT JOIN PROFILES p ON u.id = p.user_id
            WHERE u.email = :email");

            // Bind email value
            $this->db->bind(':email', $email);

            // Get the user record
            $row = $this->db->single();

            // If no user found
            if (!$row) {
                return false;
            }

            // Verify password
            if (password_verify($password, $row->password)) {
                $this->logger->debug("Models/User->login($email, $password): login success");
                // Set properties
                $profileDTO = new ProfileDTO(id: $row->profile_id,
                    fullName: $row->full_name,
                    email: $row->email,
                    profilePicture: $row->profile_picture,
                    userId: $row->id
                );
                return $profileDTO;
            } else {
                return false;
            }
        }catch(\Exception $e) {
            $this->logger->error("Models/User->login(): Error: " . $e->getMessage());
            throw $e;
        }  
    }
    
    /**
     * Find user by email
     * 
     * @param string $email User email to search for
     * @return bool True if user exists, false otherwise
     */
    public function findUserByEmail($email) {
        $this->db->query("SELECT * FROM USERS WHERE email = :email");
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        // Check if email exists
        return !empty($row);
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return mixed User object if found, false otherwise
     */
    public function getUserById($id) {
        $this->db->query("SELECT u.*, p.id as profile_id, p.full_name, p.date_of_birth, p.profile_picture 
                          FROM USERS u
                          LEFT JOIN PROFILES p ON u.id = p.user_id
                          WHERE u.id = :id");
        
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if ($row) {
            return $this->setUserProperties($row);
        } else {
            return false;
        }
    }
    
    /**
     * Set userDTO object properties from database row
     * 
     * @param object $row Database result row
     */
    private function setUserProperties($row) {
        $this->id = $row->id;

    }
    
    /**
     * Create or update user profile
     * 
     * @param array $data Profile data
     * @return bool Success or failure
     */
    public function updateProfile($data) {
        // Check if profile exists
        $this->db->query("SELECT id FROM PROFILES WHERE user_id = :user_id");
        $this->db->bind(':user_id', $this->id);
        $existingProfile = $this->db->single();
        
        if ($existingProfile) {
            // Update existing profile
            $this->db->query("UPDATE PROFILES SET 
                              full_name = :full_name, 
                              date_of_birth = :date_of_birth, 
                              profile_picture = :profile_picture 
                              WHERE user_id = :user_id");
        } else {
            // Create new profile
            $this->db->query("INSERT INTO PROFILES 
                             (user_id, full_name, date_of_birth, profile_picture) 
                             VALUES 
                             (:user_id, :full_name, :date_of_birth, :profile_picture)");
        }
        
        // Bind values
        $this->db->bind(':user_id', $this->id);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':date_of_birth', $data['date_of_birth']);
        $this->db->bind(':profile_picture', $data['profile_picture'] ?? null);
        
        // Execute query
        if ($this->db->execute()) {
            // Update object properties
            $this->full_name = $data['full_name'];
            $this->date_of_birth = $data['date_of_birth'];
            $this->profile_picture = $data['profile_picture'] ?? null;
            
            if (!$existingProfile) {
                // Get the new profile ID
                $this->db->query("SELECT id FROM PROFILES WHERE user_id = :user_id");
                $this->db->bind(':user_id', $this->id);
                $newProfile = $this->db->single();
                $this->profile_id = $newProfile->id;
            }
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Change user password
     * 
     * @param string $currentPassword Current password for verification
     * @param string $newPassword New password to set
     * @return bool Success or failure
     */
    public function changePassword($currentPassword, $newPassword) {
        // Verify current password
        if (!password_verify($currentPassword, $this->password)) {
            return false;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password in database
        $this->db->query("UPDATE USERS SET password = :password WHERE id = :id");
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $this->id);
        
        if ($this->db->execute()) {
            $this->password = $hashedPassword;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a new user with profile
     * 
     * @param string $email User email address
     * @param string $password User password (plain text, will be hashed)
     * @param string|null $fullName User's full name (optional)
     * @param string|null $dateOfBirth User's date of birth in YYYY-MM-DD format (optional)
     * @param array|null $profilePicture Optional profile picture upload data ($_FILES array)
     * @return ProfileDTO|bool ProfileDTO object if creation succeeds, false otherwise
     */
    public function createUser(
        string $email,
        string $password,
        string $fullName,
        ?string $dateOfBirth = null,
        ?array $profilePicture = null
    ): array {
        $this->logger->debug("Models/User()->createUser($email, $password, $fullName, $dateOfBirth)");

        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Check if user with email already exists
            if ($this->findUserByEmail($email)) {
                $this->logger->warning("Models/User()->createUser(): Email already exists: {$email}");
                return ['success' => false, 'message' => "Email already exists: {$email}"];
            }
            
            // Prepare query to insert new user
            $this->db->query("INSERT INTO USERS (email, password) VALUES (:email, :password)");
            
            // Hash password before storing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $this->logger->debug("Models/User()->createUser(): hash password $password ==> $hashedPassword");

            // Bind values
            $this->db->bind(':email', $email);
            $this->db->bind(':password', $hashedPassword);
            
            // Execute query to create user
            if (!$this->db->execute()) {
                throw new \Exception("Failed to create user");
            }
            
            // Get the newly created user ID
            $userId = $this->db->getLastInsertId();
            $this->logger->debug("Models/User()->createUser(): user inserted => $userId");

            // Process profile picture if provided
            $profilePicturePath = null;
            if ($profilePicture && isset($profilePicture['tmp_name']) && !empty($profilePicture['tmp_name'])) {
                $profilePicturePath = $this->saveProfilePicture($userId, $profilePicture);
                if (!$profilePicturePath) {
                    $this->logger->debug("Models/User()->createUser(): Failed to save profile picture");
                    throw new \Exception("Failed to save profile picture");
                }
            }
            $this->logger->debug("Models/User()->createUser(): profilePicturePath => $profilePicturePath");

            // Create initial profile
            $this->db->query("INSERT INTO PROFILES (user_id, full_name, date_of_birth, profile_picture) 
                            VALUES (:user_id, :full_name, :date_of_birth, :profile_picture)");
            
            // Bind profile values
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':full_name', $fullName);
            $this->db->bind(':date_of_birth', $dateOfBirth);
            $this->db->bind(':profile_picture', $profilePicturePath ?? 'profile_picture_placeholder.png');
            
            // Execute query to create profile
            if (!$this->db->execute()) {
                throw new \Exception("Failed to create profile");
            }
            
            // Get profile ID
            $profileId = $this->db->getLastInsertId();
            
            // Commit transaction
            $this->db->commit();
            
            // Create and return ProfileDTO
            $this->logger->debug("Models/User()->createUser(): User created successfully: ID {$userId}");
            
            return ['success' => true,
            'profileDTO' => new ProfileDTO(
                id: $profileId,
                fullName: $fullName,
                email: $email,
                profilePicture: $profilePicturePath,
                userId: $userId
            )];
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            $this->logger->error("Models/User()->createUser(): Error: " . $e->getMessage());
            return ['success' => false, 'message' => "Error: " . $e->getMessage()];
        }
    }

    /**
     * Save profile picture to the server and return file path
     * 
     * @param int $userId User ID
     * @param array $fileData File upload data ($_FILES array)
     * @return string|bool Path to saved file or false on failure
     */
    private function saveProfilePicture(int $userId, array $fileData): string|bool {
        try {
            $this->logger->debug("Models/User()->saveProfilePicture($userId)");
            $this->logger->debug("Models/User()->saveProfilePicture(): input file: " . json_encode($fileData, JSON_PRETTY_PRINT));

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!isset($fileData['tmp_name']) || empty($fileData['tmp_name'])) {
                return false;
            }
            
            $fileType = $fileData['type'][0];
            $this->logger->debug("Models/User()->saveProfilePicture(): file type: $fileType");

            $fileExtension = pathinfo($fileData['name'][0], PATHINFO_EXTENSION);
            $this->logger->debug("Models/User()->saveProfilePicture(): file extension: $fileExtension");

            if (!in_array($fileType, $allowedTypes)) {
                $this->logger->warning("Models/User()->saveProfilePicture(): Invalid file type: {$fileType}");
                return false;
            }
            
            // Set paths
            $profilePicturesDir = MEDIA_PATH . '/profilePictures/';
            
            $this->logger->debug("Models/User()->saveProfilePicture(): dir path: $profilePicturesDir");
            // Create directory if it doesn't exist
            if (!is_dir($profilePicturesDir)) {
                $this->logger->debug("Models/User()->saveProfilePicture(): dir path doesn't exists -> create");
                mkdir($profilePicturesDir, 0755, true);
            }
            
            // Create filename
            $fileName = $userId . '.' . $fileExtension;
            $filePath = $profilePicturesDir . $fileName;
            
            $this->logger->debug("Models/User()->saveProfilePicture(): file path: $filePath");

            // Move uploaded file
            if (move_uploaded_file($fileData['tmp_name'][0], $filePath)) {
                $this->logger->debug("Models/User()->saveProfilePicture(): file saved successfully");
                // Return the database path (relative path)
                return 'profilePictures/' . $fileName;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("Models/User()->saveProfilePicture(): Error: " . $e->getMessage());
            return false;
        }
    }
}