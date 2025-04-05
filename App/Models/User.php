<?php

namespace App\Models;

use App\Core\Model;
use App\DTOs\ProfileDTO;
use Exception;


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
                    userId: $row->id,
                    isAdmin: $row->is_admin
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
        try{
            $this->db->query("SELECT u.*
                            , p.id as profile_id
                            , p.full_name
                            , p.date_of_birth
                            , p.profile_picture 
                            FROM USERS u
                            LEFT JOIN PROFILES p ON u.id = p.user_id
                            WHERE u.id = :id");
            
            $this->db->bind(':id', $id);
            
            // Execute the query and get the result
            $this->db->execute();
            $result = $this->db->resultSetAssoc();
            
            // Check if profile was found
            if (empty($result)) {
                $this->logger->warning("Models/User->getUserById($id): User not found");
                throw new Exception("User with id: $id not found");
            }
            
            $userData = $result[0];
            $this->logger->debug("Models/Profile->getUserById($id): User found: " . $userData['full_name']);
            
            // Create and return a ProfileDTO instance
            return new ProfileDTO(
                $userData['profile_id'],
                $userData['full_name'],
                $userData['profile_picture'],
                $userData['id'],
                $userData['date_of_birth']
            );
        }catch (Exception $e) {
            $this->logger->error("Models/Profile->getUserById(): Error: " . $e->getMessage());
            throw $e;
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

    /**
     * Retrieve a list of user profiles based on a search phrase and sorting criteria.
     *
     * This method searches for profiles whose full names match the provided search phrase
     * (partial matching supported). It also retrieves the total number of posts and reports
     * associated with each profile. The results can be sorted by full name, total posts,
     * or total reports in ascending order.
     *
     * @param string $searchPhrase The search term to filter profiles by full name.
     * @param string $sort The sorting field ('full_name', 'totalPosts', 'totalReports').
     * @return array An array of ProfileDTO objects representing matching profiles.
     * @throws \Exception If an error occurs during database operations.
     */
    public function getUsers(string $searchPhrase = '', string $sort = 'full_name', string $sortDirection = 'ASC'): array {
        $this->logger->debug("Models/User->getUsers($searchPhrase, $sort, $sortDirection)");
        
        // Validate sort parameter
        $validSortFields = ['full_name', 'totalPosts', 'totalReports', 'profile_id'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'full_name';
        
        try {
            // Query to fetch users and their profile details
            $sortDirection = strtoupper(trim($sortDirection));
            if($sortDirection != 'ASC' && $sortDirection != 'DESC'){
                $sortDirection = 'ASC';
            }
            $this->logger->debug("Models/User->getUsers() sortDirection: $sortDirection");

            $this->db->query("
                SELECT 
                    p.id AS profile_id, 
                    p.full_name, 
                    p.profile_picture, 
                    u.id AS user_id, 
                    u.email, 
                    u.is_admin, 
                    p.date_of_birth,
                    (SELECT COUNT(*) FROM POSTS WHERE profile_id = p.id AND is_deleted = FALSE) AS totalPosts,
                    (SELECT COUNT(*) FROM PROFILE_REPORTS WHERE reported_profile_id = p.id) AS totalReports
                FROM PROFILES p
                LEFT JOIN USERS u ON p.user_id = u.id
                WHERE LOWER(p.full_name) LIKE :searchPhrase
                ORDER BY $sortField $sortDirection
            ");
            
            // Bind search phrase with wildcard for partial matching
            $searchPhrase = strtolower(str_replace(' ', '%', trim($searchPhrase)));
            $this->db->bind(':searchPhrase', "%$searchPhrase%");
            
            // Execute and fetch results
            $rows = $this->db->resultSetObj();
            
            // Convert results into ProfileDTO objects
            $profiles = [];
            foreach ($rows as $row) {
                $profiles[] = new ProfileDTO(
                    id: $row->profile_id,
                    fullName: $row->full_name,
                    profilePicture: $row->profile_picture,
                    userId: $row->user_id,
                    email: $row->email,
                    isAdmin: $row->is_admin,
                    dateOfBirth: $row->date_of_birth,
                    totalPosts: $row->totalPosts,
                    totalReports: $row->totalReports
                );
            }
            
            return $profiles;
        } catch (\Exception $e) {
            $this->logger->error("Models/User->getUsers(): Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Block a user until a specified date
     * 
     * @param int $userId The ID of the user to block
     * @param string $blockedUntil Date until which the user will be blocked (YYYY-MM-DD format)
     * @return bool True if successful, false otherwise
     * @throws \Exception If an error occurs during the operation
     */
    public function blockUser(int $userId, string $blockedUntil): bool {
        $this->logger->debug("Models/User->blockUser($userId, $blockedUntil)");
        
        try {
            // Validate the date format
            $date = \DateTime::createFromFormat('Y-m-d', $blockedUntil);
            if (!$date || $date->format('Y-m-d') !== $blockedUntil) {
                $this->logger->warning("Models/User->blockUser(): Invalid date format: $blockedUntil");
                throw new \Exception("Invalid date format. Expected YYYY-MM-DD.");
            }
            
            // Check if the date is in the future
            $today = new \DateTime();
            if ($date < $today) {
                $this->logger->warning("Models/User->blockUser(): Block date must be in the future");
                throw new \Exception("Block date must be in the future.");
            }
            
            // Check if user exists
            $this->db->query("SELECT id FROM USERS WHERE id = :id");
            $this->db->bind(':id', $userId);
            $user = $this->db->single();
            
            if (!$user) {
                $this->logger->warning("Models/User->blockUser(): User not found with ID: $userId");
                throw new \Exception("User not found with ID: $userId");
            }
            
            // Update the blocked_until field
            $this->db->query("UPDATE USERS SET blocked_until = :blocked_until WHERE id = :id");
            $this->db->bind(':blocked_until', $blockedUntil);
            $this->db->bind(':id', $userId);
            
            if ($this->db->execute()) {
                $this->logger->info("Models/User->blockUser(): User $userId blocked until $blockedUntil");
                return true;
            } else {
                $this->logger->warning("Models/User->blockUser(): Failed to update database");
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Models/User->blockUser(): Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Unblock a user by clearing their blocked_until date
     * 
     * @param int $userId The ID of the user to unblock
     * @return bool True if successful, false otherwise
     * @throws \Exception If an error occurs during the operation
     */
    public function unblockUser(int $userId): bool {
        $this->logger->debug("Models/User->unblockUser($userId)");
        
        try {
            // Check if user exists
            $this->db->query("SELECT id FROM USERS WHERE id = :id");
            $this->db->bind(':id', $userId);
            $user = $this->db->single();
            
            if (!$user) {
                $this->logger->warning("Models/User->unblockUser(): User not found with ID: $userId");
                throw new \Exception("User not found with ID: $userId");
            }
            
            // Set blocked_until to NULL to unblock the user
            $this->db->query("UPDATE USERS SET blocked_until = NULL WHERE id = :id");
            $this->db->bind(':id', $userId);
            
            if ($this->db->execute()) {
                $this->logger->info("Models/User->unblockUser(): User $userId has been unblocked");
                return true;
            } else {
                $this->logger->warning("Models/User->unblockUser(): Failed to update database");
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Models/User->unblockUser(): Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if a user is currently blocked
     * 
     * @param int $userId The ID of the user to check
     * @return array Information about the user's block status
     * @throws \Exception If an error occurs during the operation
     */
    public function isUserBlocked(int $userId): array {
        $this->logger->debug("Models/User->isUserBlocked($userId)");
        
        try {
            $this->db->query("SELECT blocked_until FROM USERS WHERE id = :id");
            $this->db->bind(':id', $userId);
            $user = $this->db->single();
            
            if (!$user) {
                $this->logger->warning("Models/User->isUserBlocked(): User not found with ID: $userId");
                throw new \Exception("User not found with ID: $userId");
            }
            
            $today = date('Y-m-d');
            $isBlocked = !empty($user->blocked_until) && $user->blocked_until > $today;
            
            return [
                'isBlocked' => $isBlocked,
                'blockedUntil' => $user->blocked_until ?? null,
                'remainingDays' => $isBlocked ? (strtotime($user->blocked_until) - strtotime($today)) / (60 * 60 * 24) : 0
            ];
        } catch (\Exception $e) {
            $this->logger->error("Models/User->isUserBlocked(): Error: " . $e->getMessage());
            throw $e;
        }
    }
}