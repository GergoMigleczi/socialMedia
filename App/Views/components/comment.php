<?php 
// At the top of home.php, before any HTML output
use App\Components\ProfileComponent;

// Pre-initialize all components
ProfileComponent::init();
?>
<div class="card mb-3 rounded-3 p-3 text-start">
    <div class="d-flex">
        <div class="d-flex align-items-top justify-content-top">
            <?php ProfileComponent::renderProfilePicture($comment->profile);?>
        </div>
        <div class="d-flex flex-column flex-grow-1">
            <?php ProfileComponent::renderProfileName($comment->profile);?>
            <p class="m-0 ps-3"><?=$comment->content?></p>
            <p class="text-end m-0 text-muted"><?=$comment->date?></p>
        </div>
    </div>
</div>