<?php 
// At the top of home.php, before any HTML output
use Core\AssetManager;
use Components\MessageComponent;
use Components\ProfileComponent;

// Add Page's styles and scripts
AssetManager::addStyle('chat-style', '/socialMedia/public/assets/css/chat.css');
AssetManager::addScript('bootstrap-icons-js', "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");
AssetManager::addScript('chat-script', "/socialMedia/public/assets/js/pages/chat.js");

// Pre-initialize all components
ProfileComponent::init();
MessageComponent::init();
?>

<!-- Chat body section -->
<div class="container d-flex flex-column h-100 py-3">
  <!-- Back button -->
  <div class="mb-2">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
  <!-- Profile info at the top -->
  <div class="card-header bg-white border-bottom d-flex align-items-center py-2 shadow-sm rounded-top p-3">
    <div class="d-flex align-items-center">
      <?php ProfileComponent::renderProfilePicture($profile);?>
      <?php ProfileComponent::renderProfileName($profile);?>
    </div>
  </div>
  
  <!-- Chat messages area (scrollable) -->
  <div id="chat-messages" class="card-body bg-light flex-grow-1 overflow-auto p-3">
    <?php 
      if ((!empty($messages))){
          foreach ($messages as $message) {
              MessageComponent::render($message, $loggedInProfileId);
          }
          if($isBlocked){
            echo '<div class="alert alert-info">You cannot chat as either you blocked this profile or they blocked you.</div>';
          }
      }else {
        if($isBlocked){
          echo '<div class="alert alert-info">You cannot chat as either you blocked this profile or they blocked you.</div>';
        }else{
          echo '<div class="alert alert-info">No messages.</div>';
        }  
      }
    ?>
  </div>
  
  <!-- Message input area (fixed at bottom) -->
  <div class="card-footer bg-white border-top-0 mt-auto p-2 shadow-sm rounded-bottom">
    <form id="message-form" class="flex-grow-1" data-chat-id="<?= $chatId ?>">
      <div class="input-group">
        <input id="message-input" type="text" name="message" class="form-control border-0 bg-light" placeholder="Type a message..." required <?=$isBlocked ? 'disabled' : ''?>>
        <button id="message-submit-button" type="submit" class="btn btn-primary rounded-circle ms-2" style="width: 40px; height: 40px;" <?=$isBlocked ? 'disabled' : ''?>>
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </form>
  </div>
</div>