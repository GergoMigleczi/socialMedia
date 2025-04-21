<?php 
// At the top of home.php, before any HTML output
use App\Components\ChatCardComponent;

// Pre-initialize all components
ChatCardComponent::init();

?>

<div class="container d-flex flex-column py-3" style="max-width: 800px;">
    <h2 class="mb-3 ps-2 text-center">Chats</h2>
    
    <div>
        <?php 
            if ((!empty($chats))){
                foreach ($chats as $chat) {
                    ChatCardComponent::render($chat);
                }
            }else {
                echo '<div class="alert alert-info">No chats.</div>';
            }
        ?>
    </div>
</div>