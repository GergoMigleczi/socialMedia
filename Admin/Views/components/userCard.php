<?php 
// At the top of home.php, before any HTML output
use App\Components\ProfileComponent;
// Pre-initialize all components
ProfileComponent::init();

?>
<div class="card my-2"
    role="button"
    onclick="window.location.href='/socialMedia/admin/user/<?=$profile->userId?>';"
>
    <div class="d-flex align-items-center">
        <?php ProfileComponent::renderProfilePicture($profile);?>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
                <?php ProfileComponent::renderProfileName($profile);?>
                <div class="d-flex">
                    <span class="stats-badge badge bg-primary mx-1"><?= $profile->totalPosts?> Posts</span>
                    <span class="stats-badge badge bg-danger mx-1"><?= $profile->totalReports?> Reports</span>
                </div>
            </div>
            <small class="text-muted">ID: <?= $profile->userId?></small>
        </div>
    </div>    
</div> 