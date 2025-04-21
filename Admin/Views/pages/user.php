<?php 
// At the top of home.php, before any HTML output
use Admin\Components\PostChartComponent;
use App\Components\ProfileComponent;
use Admin\Components\ReportCardComponent;
use App\Core\AssetManager;
// Pre-initialize all components
ProfileComponent::init();
PostChartComponent::init();

//AssetManager::addScript('user-script', '/socialMedia/public/assets/adminjs/pages/user.js');
AssetManager::addStyle('profile-style', '/socialMedia/public/assets/css/profile.css');
AssetManager::addScript('user-script', '/socialMedia/public/assets/adminjs/pages/user.js');

?>
<div class="container-fluid px-3 mt-3 top" style="max-width: 900px;">
    <div class="my-2">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    <div class="card p-4 my-2 d-flex flex-column align-items-center justify-content-center">
        <img class="profile-picture d-block mx-auto mx-md-0" 
            src="<?= base_url('/public/getImage?url=' . $profile->profilePicture) ?>" 
            alt="Profile image">

        <h2 class="mb-3 text-center text-md-start d-inline-block"><?=$profile->fullName?></h2>

        <?php if($isBlocked): ?>
            <small class="text-danger">Blocked Until: <?=$blockedUntil?></small>
        <?php endif ?>
        <div class=" d-flex my-3">
            <a role="button" href="/socialMedia/public/profile/<?=$profile->id?>" class="btn btn-outline-primary me-2">View Profile</a>
            <?php if($isBlocked): ?>
                <button id="unblock-user-btn" type="button" class="btn btn-outline-secondary me-2" data-user-id="<?=$profile->userId?>">Unblock User</button>
            <?php else: ?>
                <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#blockUserModal">Block User</button>
            <?php endif ?>
            <button id="delete-user-btn" class="btn btn-outline-dark" data-user-id="<?=$profile->userId?>">Delete User</button>
        </div>
    </div>
    <div class="card p-4 my-4">
        <?php PostChartComponent::render($profile->id)?>
    </div>
    <div class="card p-4 my-2">
        <h4 class="mt-4">Reports Filed Against User</h4>
        <div>
            <?php 
            if(!empty($reports)){
                foreach($reports as $report){
                    ReportCardComponent::render($report);
                }
            }else{
                echo '<div class="alert alert-info">No reports.</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal with Calendar -->
<div class="modal fade" id="blockUserModal" tabindex="-1" aria-labelledby="blockUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blockUserModalLabel">Block User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="blockUserForm" data-user-id="<?=$profile->userId?>">
        <div class="modal-body">
            <div class="mb-3">
                <label for="blockUntilDate" class="form-label">Block Until:</label>
                <input type="date" class="form-control" id="blockUntilDate" min="<?php echo date('Y-m-d');?>" required>
                <small class="text-muted">Select the date until which the user will be blocked</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Confirm Block</button>
        </div>
      </form>
    </div>
  </div>
</div>
