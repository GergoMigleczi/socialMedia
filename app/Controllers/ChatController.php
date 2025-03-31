<?php
namespace Controllers;
use Core\Controller;
use Core\View;
use Exception;
use Models\Chat;
use Components\MessageComponent;
use DTOs\MessageDTO;
use Models\Friend;
use Models\ProfileBlocking;

/**
 * ChatController handles all chat-related functionality including displaying chats,
 * managing messages, and creating private conversations between users.
 */
class ChatController extends Controller
{
    private $logFile = 'chat.log';
    private $chatModel;
    private $profileBlockingModel;
    private $friendModel;
    
    /**
     * Initialize controller with required models and parent setup
     */
    public function __construct()
    {
      // Call parent constructor with specific log file
      parent::__construct($this->logFile);
      $this->chatModel = new Chat($this->logFile);
      $this->profileBlockingModel = new ProfileBlocking($this->logFile);
      $this->friendModel = new Friend($this->logFile);
    }
    
    /**
     * Display all chats associated with the logged-in user
     * Requires authentication
     */
    public function showChats()
    {
      $this->requireAuth(true);

      try{
        // Fetch all chats for the current logged-in profile
        $chats = $this->chatModel->getChatsForProfile($this->session->getProfileId());
        
        // Render the chats list view
        View::render(
          'pages/chats',
          [
            'title' => 'Chats',
            'chats' => $chats
          ]
        );
      }catch(Exception $e){
        $this->logger->error("Controllers/ChatController->showChats(): " . $e->getMessage());
        $this->redirect('500');
      }
    }

    /**
     * Display a specific chat and its messages
     * Requires authentication and validates user participation in the chat
     * 
     * @param int $chatId The ID of the chat to display
     */
    public function showChat($chatId)
    {
      $this->requireAuth(true);

      try{
        $loggedInProfileId = $this->session->getProfileId();
      
        // Get all messages for this chat
        $messages = $this->chatModel->getMessagesForChat($chatId);
        
        // Validate that logged-in user is a participant and get the other participant
        $validation = $this->chatModel->validateAndGetOtherParticipant($chatId, $loggedInProfileId);
        if ($validation['isParticipant']) {
          $profile = $validation['otherParticipant'];
        } else {
          // User is not authorized to view this chat
          $this->denyAccess();
        }
        
        // Check blocking status in both directions
        $isBlockedByLoggedInProfile = $this->profileBlockingModel->isProfileBlocked($loggedInProfileId, $profile->id);
        $iLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked($profile->id, $loggedInProfileId); 

        // Render the chat view with all necessary data
        View::render(
          'pages/chat',
          [
            'title' => 'Chat',
            'chatId' => $chatId,
            'profile' => $profile,
            'messages' => $messages,
            'loggedInProfileId' => $loggedInProfileId,
            'isBlocked' => $isBlockedByLoggedInProfile || $iLoggedInProfileBlocked
          ]
        );
      }catch(Exception $e){
            $this->logger->error("Controllers/ChatController->showChat(): " . $e->getMessage());
            $this->redirect('500');
      }
      
    }

    /**
     * API endpoint to create a new message in a chat
     * Validates permissions, blocking status, and content before saving
     * 
     * @param int $chatId The ID of the chat to add a message to
     */
    function createMessage($chatId){
      $this->logger->debug("Controllers/ChatController->createMessage($chatId)");

      // Verify user is authenticated for API access
      $loggedInProfileId = $this->apiAuthLoggedInProfile();

      // Validate chat ID
      $chatId = intval($chatId);
      if (!$chatId) {
        $this->sendBadRequest('Invalid chat ID');
      }

      // Verify user is a participant in this chat
      if (!$this->chatModel->isProfileInChat($chatId, $loggedInProfileId)) {
        $this->sendForbidden('User not authorised to add to this chat');
      }

      try{
        // Get the other participant to check blocking status
        $otherParticipants = $this->chatModel->getChatParticipants($chatId, $loggedInProfileId);
        $otherParticipant = $this->chatModel->getOtherParticipant($otherParticipants, $loggedInProfileId);
        
        // Check if logged-in user is blocked by the other user
        if($this->profileBlockingModel->isProfileBlocked($otherParticipant->id, $loggedInProfileId)){
          $this->sendForbidden("Failed to send message. You are blocked by this user.");
        }
        
        // Check if logged-in user is blocking the other user
        if($this->profileBlockingModel->isProfileBlocked($loggedInProfileId, $otherParticipant->id)){
          $this->sendForbidden("Failed to send message. You are blocking this user.");
        }
      }catch(Exception $e){
        $this->logger->error("Controllers/ChatController->createMessage(): Failed to get block status between the profiles: " . $e->getMessage());
        $this->sendInternalServerError("Failed to get block status between the profiles: " . $e->getMessage());
      }
      
      // Verify content type and extract input
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      $input = $this->extractInput($contentType);
      
      // Get message content from POST data
      $content = $input['content'] ?? '';

      // Validate message content
      if (!$content) {
        $this->sendBadRequest('Missing required fields');
      }

      // Get requested return format (html or json)
      $format = $input['returnFormat'] ?? 'json';

      try{
        // Save message to database and get the new message DTO
        $messageDTO = $this->chatModel->saveMessage($chatId, $loggedInProfileId, $content);

        if ($format === 'html') {
          // Generate HTML for the new message
          $html = '';
          $html = $this->getMessageHtml($messageDTO, $loggedInProfileId);
          // Return HTML response
          http_response_code(201);
          header('Content-Type: text/html');
          echo $html;
          exit;
        } else {
          // Return JSON response (default)
          http_response_code(201);
          header('Content-Type: application/json');
          echo json_encode([
            'success' => true,
            'message' => json_encode($messageDTO)
          ]);
          exit;
        }
      }catch(Exception $e){
        $this->logger->error("Controllers/ChatController->createMessage(): Failed to get block status between the profiles: " . $e->getMessage());
        $this->sendInternalServerError();
      }
    }

    /**
     * Generate HTML representation of a message
     * 
     * @param MessageDTO $message The message data
     * @param int $profileId The current user's profile ID
     * @return string HTML content for the message
     */
    public function getMessageHtml(MessageDTO $message, int $profileId){
      // Capture the output of the include
      ob_start();
      // Render the message component
      MessageComponent::render($message, $profileId); // Template that includes profilePicture.php and profileName.php
      return ob_get_clean();
    }

    /**
     * API endpoint to get an existing private chat between two profiles
     * 
     * @param int $profileId Profile ID of the target user
     * @return void
     */
    public function getPrivateChat($profileId)
    {
        // Get current logged-in user's profile ID
        $currentProfileId = $this->apiAuthLoggedInProfile();

        // Validate input profile ID
        $profileId = intval($profileId);
        if (!$profileId) {
            $this->sendBadRequest('Invalid profile ID');
        }

        $this->logger->debug("Controllers/ChatController->getPrivateChat(): profileId: $profileId, currentProfileId: $currentProfileId");
        try {
            // Check for existing private chat between the two users
            $chatId = $this->chatModel->getPrivateChatId($currentProfileId, $profileId);
            $this->logger->debug("Controllers/ChatController->getPrivateChat(): chatId: $chatId");

            if ($chatId) {
                // Existing chat found - return its ID
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'chatId' => $chatId
                ]);
                exit;
            }else{
              // No existing chat found between these users
              http_response_code(200);
              header('Content-Type: application/json');
              echo json_encode([
                  'success' => false,
                  'chatId' => null,
                  'message' => 'No existing chat found'
              ]);
              exit;
            }
        } catch (Exception $e) {
            $this->logger->error('Error getting private chat: ' . $e->getMessage());
            $this->sendInternalServerError();
        }
    }

    /**
     * API endpoint to create a new private chat between two profiles
     * Validates friendship status before creating
     * 
     * @return void
     */
    public function createPrivateChat()
    {
        // Get current logged-in user's profile ID
        $currentProfileId = $this->apiAuthLoggedInProfile();
        
        // Verify content type and extract input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get target profile ID from POST data
        $profileId = $input['profileId'] ?? '';

        // Validate target profile ID
        $profileId = intval($profileId);
        if (!$profileId) {
          $this->sendBadRequest('Invalid profile ID');
        }

        try {
            // Verify users are friends (required to start a chat)
            $isFriend = $this->friendModel->isFriend($profileId, $currentProfileId);
            if(!$isFriend){
                $this->sendForbidden('Chats are only allowed between friends');
            }
            
            // Check if chat already exists (prevent duplicates)
            $existingChatId = $this->chatModel->getPrivateChatId($currentProfileId, $profileId);
            
            if ($existingChatId) {
                // Chat already exists - return existing ID
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'chatId' => $existingChatId,
                    'message' => 'Chat already exists'
                ]);
                exit;
            }

            // Create new private chat between the two users
            $newChatId = $this->chatModel->createPrivateChat($currentProfileId, $profileId);

            // Respond with new chat ID
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'chatId' => $newChatId
            ]);
            exit;

        } catch (Exception $e) {
            $this->logger->error('Error creating private chat: ' . $e->getMessage());
            $this->sendInternalServerError();
        }
    }
}