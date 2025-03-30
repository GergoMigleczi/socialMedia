
<?php 
// At the top of home.php, before any HTML output
use Core\AssetManager;
use Components\FriendCardComponent;

// Pre-initialize all components
FriendCardComponent::init();
?>

<div class="container d-flex flex-column py-3" style="max-width: 700px;">
    <!-- Back button -->
    <div class="mb-2">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
        </a>
    </div> 

    <!-- Title -->   
    <h1 class="text-center mb-4">Friends</h1>
    
    <!-- Friend Cards -->
    <?php 
        if ((!empty($friends))){
            foreach ($friends as $friend) {
                FriendCardComponent::render($friend);
            }
        }else {
            echo '<div class="alert alert-info">No friends.</div>';
        }
    ?>
</div>