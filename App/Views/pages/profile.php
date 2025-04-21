<?php 
// At the top of home.php, before any HTML output
use App\Components\ChatButtonComponent;
use App\Components\PostComponent;
use App\Components\FriendButtonComponent;
use App\Core\AssetManager;

// Add Page's styles and scripts
//AssetManager::addScript('profile-script', '/socialMedia/public/assets/js/pages/home.js');
AssetManager::addScript('profile-script', '/socialMedia/public/assets/js/pages/profile.js');
AssetManager::addStyle('profile-style', '/socialMedia/public/assets/css/profile.css', );

// Pre-initialize all components
PostComponent::init();
FriendButtonComponent::init();
ChatButtonComponent::init();
?>

<div class="container-fluid px-3 mt-3 top" >
    
    <div class="card mb-3">
        <div class="card-body text-center text-md-start d-md-flex align-items-center justify-content-between">
            <img class="profile-picture d-block mx-auto mx-md-0" 
                src="<?= base_url('/public/getImage?url=' . $profile->profilePicture) ?>" 
                alt="Profile image">

            <h2 class="mb-3 mb-md-0 text-center text-md-start"><?=$profile->fullName?></h2>

            <div class="profile-action-container text-center text-md-end">
                <?php if ($isOwnProfile): ?>
                    <!-- Show edit profile, settings buttons -->
                    <a class="btn btn-primary px-4" href="/socialMedia/public/profile/<?=$profile->id?>/friends">Friends</a>
                <?php else: ?>
                    <?php ChatButtonComponent::render($profile->id, $displayChatBtn)?>
                    <span id="friend-action-container">
                        <?php FriendButtonComponent::render($profile->id, $friendshipStatus, $displayFriendBtn)?>
                    </span>
                    <button id="block-profile-btn" class="btn <?=$isBlockedByLoggedInProfile ? 'btn-secondary' : 'btn-danger'?> px-4 mx-1" data-profile-id="<?=$profile->id?>"><?=$isBlockedByLoggedInProfile ? 'Unblock' : 'Block'?></button>
                    <button id="report-profile-btn" class="btn btn-danger px-4 mx-1" data-bs-toggle="modal" data-bs-target="#reportUserModal" data-profile-id="<?=$profile->id?>">Report</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Posts Container -->
    <div class="row">
        <div class="col">
            <?php 
                if ((!empty($posts))){
                    foreach ($posts as $index => $post) {
                        PostComponent::render($post);
                    }
                }else {
                    echo '<div class="alert alert-info">No posts to display.</div>';
                }
            ?>
        </div>   
    </div>
</div>

<!-- Report User Modal -->
<div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reportUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportUserModalLabel">Report User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <!-- Hidden input for profileId -->
                        <input type="hidden" id="profileId" name="profileId" value="<?=$profile->id?>">

                        <div class="mb-3">
                            <label for="reportReason" class="form-label">Reason</label>
                            <select class="form-select" id="reportReason"  name="reportReason" required>
                                <?php foreach ($reportOptions as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>">
                                        <?php echo ucfirst(htmlspecialchars($option)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reportDetails" class="form-label">Additional Details (Optional)</label>
                            <textarea class="form-control" id="reportDetails"  name="reportDetails" rows="3"></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="confirmReportBtn">Report</button>
                </div>
            </form>
        </div>
    </div>
</div>



