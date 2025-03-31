<?php 
// At the top of home.php, before any HTML output
use App\Components\ChatButtonComponent;
use App\Components\FriendButtonComponent;
use App\Components\ProfileComponent;
// Pre-initialize all components
ProfileComponent::init();
FriendButtonComponent::init();
ChatButtonComponent::init();

?>

<!-- Friend Card -->
<div class="friend-card card mb-3 rounded-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <?php ProfileComponent::renderProfilePicture($friend);?>
            <?php ProfileComponent::renderProfileName($friend);?>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <?= ChatButtonComponent::render($friend->id)?>
            <?= FriendButtonComponent::render($friend->id, 'Friends'); ?>
            <!--<button class="btn btn-danger" data-user-id="<?=$friend->id ?>">Block</button>-->
        </div>
    </div>
</div>