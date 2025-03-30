<?php 
// At the top of home.php, before any HTML output
use Components\ProfileComponent;

// Pre-initialize all components
ProfileComponent::init();
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <?php ProfileComponent::renderProfilePicture($post->profile);?>
            <div>
                <?php ProfileComponent::renderProfileName($post->profile);?>
                <small class="text-muted d-flex align-items-center">
                    <?= $post->date ?>
                    <?php if(isset($post->location) && !empty($post->location)): ?>
                        <i class="bi bi-geo-alt ms-2"></i> <?= $post->location ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <?php if(isset($post->content) && !empty($post->content)): ?>
            <p class="card-text">
                <?= $post->content ?>
            </p>
        <?php endif; ?>

        <!-- Only show carousel container if there are images -->
        <?php if (!empty($post->images) && count($post->images) > 0): ?>
        <div class="mb-3">
            <!-- Bootstrap Carousel - Note: no ID is set here, it will be set by JS -->
            <div class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                <!-- Carousel Navigation Arrows -->
                <button class="carousel-control-prev" type="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <!-- Carousel Items -->
                <div class="carousel-inner">
                    <?php foreach($post->images as $index => $image): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="carousel-image-container">
                            <img src="<?= base_url('/public/getImage?url=' . $image) ?>" class="d-block" alt="Post image">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center">
            <div class="post-metrics">
                <span class="me-3">
                    <i class="bi <?= $post->likedByUser ? 'bi-heart-fill text-danger' : 'bi-heart' ?> me-1 "></i>
                    <span id="like-counter-<?= $post->id; ?>"><?= $post->likes ?></span> likes
                </span>
                <span>
                    <i class="bi bi-chat me-1" data-post-id="<?= $post->id; ?>"></i> <span id="comment-counter-<?= $post->id; ?>"><?= $post->comments ?></span> comments
                </span>
            </div>
        </div>

        <div class="mt-3 d-flex">
            <button class="btn btn-outline-secondary me-2 w-50 like-btn" data-post-id="<?= $post->id; ?>">
                <i class="bi bi-heart me-1"></i> Like
            </button>
            <button class="btn btn-outline-secondary w-50 comment-btn" data-post-id="<?= $post->id; ?>">
                <i class="bi bi-chat me-1"></i> Comment
            </button>
        </div>
        <!-- Comment Section Container -->
        <div id="comments-section-<?= $post->id; ?>" class="comment-section p-3 mb-4 mt-4 d-none">
            <!-- Add Comment Section -->
            <div class="card mb-3 rounded-3 p-3">
                <form class="flex-grow-1 comment-form" data-post-id="<?= $post->id; ?>">
                    <div class="input-group">
                        <input type="text" class="form-control comment-input" name="comment" placeholder="Write a comment..." required>
                        <button type="submit" class="btn btn-primary">Post</button>
                    </div>
                </form>
            </div>
            <div class="comment-container"></div>
        </div>
    </div>
</div>