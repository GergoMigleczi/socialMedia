<?php
namespace Controllers;
use Core\Controller;
use Core\View;
use Models\Chat;
use Components\MessageComponent;
use DTOs\MessageDTO;
use Models\Friend;
use Models\ProfileBlocking;

class ChatController extends Controller
{
    private $logFile = 'chat.log';
    private $chatModel;
    private $profileBlockingModel;
    public function __construct()
    {
      // Call parent constructor with specific log file
      parent::__construct($this->logFile);
      $this->chatModel = new Chat($this->logFile);
      $this->profileBlockingModel = new ProfileBlocking($this->logFile);
    }
    public function showChats()
    {
      $this->requireAuth(true);

      $chats = $this->chatModel->getChatsForProfile($this->session->getProfileId());

      foreach ($chats as $chat) {
        $this->logger->debug($chat->__toString());
      }
      // Render the login view
      View::render(
        'pages/chats',
        [
          'title' => 'Chats',
          'chats' => $chats
        ]
      );
    }

    public function showChat($chatId)
    {
      $this->requireAuth(true);

      $loggedInProfileId = $this->session->getProfileId();
      $messages = $this->chatModel->getMessagesForChat($chatId);
      $validation = $this->chatModel->validateAndGetOtherParticipant($chatId, $loggedInProfileId);
      if ($validation['isParticipant']) {
        $profile = $validation['otherParticipant'];
      } else {
        $this->denyAccess();
      }
      $isBlockedByLoggedInProfile = $this->profileBlockingModel->isProfileBlocked( $loggedInProfileId, $profile->id);
      $iLoggedInProfileBlocked = $this->profileBlockingModel->isProfileBlocked(  $profile->id, $loggedInProfileId); 

      // Render the login view
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
    }

    function createMessage($chatId){
      $this->logger->debug("Controllers/ChatController->createMessage($chatId)");

      // Verify user is authenticated
      $loggedInProfileId = $this->apiAuthLoggedInProfile();

      // Validate chat ID
      $chatId = intval($chatId);
      if (!$chatId) {
        http_response_code(400); // Bad Request
        echo json_encode([
          'success' => false,
          'error' => 'Invalid chat ID'
        ]);
        exit;
      }

      if (!$this->chatModel->isProfileInChat($chatId,$loggedInProfileId)) {
        http_response_code(401); // Unauthorized
        echo json_encode([
          'success' => false,
          'error' => 'User not authorised to add to this chat'
        ]);
        exit;
      }

      $otherParticipants = $this->chatModel->getChatParticipants($chatId, $loggedInProfileId);
      $otherParticipant = $this->chatModel->getOtherParticipant($otherParticipants, $loggedInProfileId);
      //Is blocked by target profile
      if($this->profileBlockingModel->isProfileBlocked($otherParticipant->id, $loggedInProfileId)){
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false,
        'message' => "Failed to send message. You are blocked by this user."]);
        exit;
      }
      if($this->profileBlockingModel->isProfileBlocked( $loggedInProfileId, $otherParticipant->id)){
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false,
        'message' => "Failed to send message. You are blocking this user."]);
        exit;
      }

      // Verify content type
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      $input = $this->extractInput($contentType);
      // Get POST body data
      $content = $input['content'] ?? '';

      if (!$content) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
          'success' => false,
          'error' => 'Missing required fields'
        ]);
        exit;
      }

      // Get format from query string
      $format = $input['returnFormat'] ?? 'json';

      // Save comment to database and get the saved comment with ID
      $messageDTO = $this->chatModel->saveMessage($chatId, $loggedInProfileId, $content);

      if ($format === 'html') {
        $html = '';
        $html = $this->getMessageHtml($messageDTO, $loggedInProfileId);
        // Return html
        http_response_code(201);
        header('Content-Type: text/html');
        echo $html;
        exit;
      } else {
        // Return as JSON (default)
        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode([
          'success' => true,
          'message' => json_encode($messageDTO)
        ]);
        exit;
      }
    }

    public function getMessageHtml(MessageDTO $message, int $profileId){
      // Capture the output of the include
      ob_start();
      // User data is available to these included files
      MessageComponent::render($message, $profileId); // Template that includes profilePicture.php and profileName.php
      return ob_get_clean();
  }

  /**
     * Get existing private chat between two profiles
     * 
     * @param int $profileId Profile ID of the target user
     * @return void
     */
    public function getPrivateChat($profileId)
    {
        // Get current logged-in user's profile ID
        $currentProfileId = $this->apiAuthLoggedInProfile();

        // Validate input
        $profileId = intval($profileId);
        if (!$profileId) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Invalid profile ID'
            ]);
            exit;
        }

        $this->logger->debug("Controllers/ChatController->getPrivateChat(): profileId: $profileId, currentProfileId: $currentProfileId");
        try {
            // Check for existing private chat
            $chatId = $this->chatModel->getPrivateChatId($currentProfileId, $profileId);
            $this->logger->debug("Controllers/ChatController->getPrivateChat(): chatId: $chatId");

            if ($chatId) {
                // Existing chat found
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'chatId' => $chatId
                ]);
                exit;
            }

            // No existing chat
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'chatId' => null,
                'error' => 'No existing chat found'
            ]);
            exit;

        } catch (\Exception $e) {
            $this->logger->error('Error getting private chat: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]);
            exit;
        }
    }

    /**
     * Create a new private chat between two profiles
     * 
     * @return void
     */
    public function createPrivateChat()
    {
        // Get current logged-in user's profile ID
        $currentProfileId = $this->apiAuthLoggedInProfile();
        
        // Verify content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = $this->extractInput($contentType);

        // Get POST body data
        $profileId = $input['profileId'] ?? '';

        // Validate input
        $profileId = intval($profileId);
        if (!$profileId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid profile ID'
            ]);
            exit;
        }

        $friendModel = new Friend();
        if(!$friendModel->isFriend($profileId, $currentProfileId)){
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Not friends, cannot open chat'
            ]);
            exit;
        }

        try {
            // Check if chat already exists (double-check)
            $existingChatId = $this->chatModel->getPrivateChatId($currentProfileId, $profileId);
            
            if ($existingChatId) {
                // Chat already exists
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'chatId' => $existingChatId,
                    'message' => 'Chat already exists'
                ]);
                exit;
            }

            // Create new private chat
            $newChatId = $this->chatModel->createPrivateChat($currentProfileId, $profileId);

            // Respond with new chat ID
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'chatId' => $newChatId
            ]);
            exit;

        } catch (\Exception $e) {
            $this->logger->error('Error creating private chat: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
            exit;
        }
    }
}