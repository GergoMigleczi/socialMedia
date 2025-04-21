<?php 
// At the top of home.php, before any HTML output
use App\Components\PostComponent;

// Pre-initialize all components
PostComponent::init();

?>

<div class="container-fluid px-3 mt-3 top">
    <!-- Add New Post Button -->
    <div class="row mb-3">
        <div class="col">
            <a 
                id="add-new-post" 
                class="btn btn-primary w-100 py-2"
                href="/socialMedia/public/addPost"
            >
                Add New Post
            </a>
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
