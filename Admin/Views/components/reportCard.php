<?php 
// At the top of home.php, before any HTML output
use App\Components\ProfileComponent;
// Pre-initialize all components
ProfileComponent::init();

?>

<div class="card my-2 p-2">
    <!-- Header with reporter info -->
    <div class="d-flex justify-content-between align-items-center report-header">
        <div class="d-flex align-items-center">
            <?php ProfileComponent::renderProfilePicture($report->reporterProfileDTO);?>
            <div>
                <?php ProfileComponent::renderProfileName($report->reporterProfileDTO);?>
                <span class="text-muted-small"><?= $report->createdAt?></span>
            </div>
        </div>
        <span class="text-danger pe-5"><?= $report->reasonType?></span>
    </div>
    
    <!-- Report description -->
    <?php if($report->description): ?>
        <div class="ps-2 mt-3">
            <h6 class="mb-2">Description</h6>
            <div class="ps-3">
                <p><?=$report->description?></p>
            </div>
        </div>
    <?php endif?>
</div>