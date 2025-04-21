<?php 
// At the top of home.php, before any HTML output
use App\Components\ProfileComponent;

// Pre-initialize all components
ProfileComponent::init();
?>

<div class="card card-hover mb-3 rounded-3 p-3 text-start text-decoration-none"
role="button"
onclick="window.location.href='/socialMedia/public/chats/<?=$chat->id?>';">
    <div class="d-flex">
        <div class="d-flex align-items-top justify-content-top">
            <?php ProfileComponent::renderProfilePicture($chat->profile);?>
        </div>
        <div class="d-flex flex-column flex-grow-1">
            <?php ProfileComponent::renderProfileName($chat->profile);?>
            <p class="m-0 ps-3"><?=$chat->lastMessage?></p>
            <p class="text-end m-0 text-muted"><?=$chat->lastMessageDate?></p>
        </div>
    </div>
</div>