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
        <div class="my-3">
            <a role="button" href="/socialMedia/public/profile/<?=$profile->id?>" class="btn btn-outline-primary me-2">View Profile</a>
            <button class="btn btn-outline-danger me-2">Block User</button>
            <button class="btn btn-outline-dark">Delete User</button>
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