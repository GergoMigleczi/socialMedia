<?php 
// At the top of home.php, before any HTML output
use Core\AssetManager;

// Add Page's styles and scripts
AssetManager::addStyle('carousel-style', '/socialMedia/public/assets/css/carousel.css');
AssetManager::addStyle('addPost-style', '/socialMedia/public/assets/css/addPost.css');
AssetManager::addScript('carousel-script', '/socialMedia/public/assets/js/components/carousel.js');
AssetManager::addScript('addPost-script', '/socialMedia/public/assets/js/pages/addPost.js');

?>

<div class="container-fluid px-3 mt-3">
    <h2 class="mb-3 ps-2 text-center">Add Post</h2>
    <div class="card py-4">
        <div class="card-body">            
            <form id="newPostForm">
                <div class="mb-3">
                    <div class="input-group">
                        <button id="current-location-btn" title="use current location" type="button" class="input-group-text btn btn-primary">
                            <i class="bi bi-geo-alt"></i>
                        </button>
                        <input type="text" class="form-control" id="location" name="location" placeholder="Add location...">
                        <input type="text" class="form-control" id="latitude" name="latitude" style="display:none">
                        <input type="text" class="form-control" id="longitude" name="longitude" style="display:none">
                    </div>
                </div>
                
                <div class="mb-3">
                    <textarea class="form-control post-textarea" id="postContent" name="postContent" placeholder="Write your post here..." required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="fileUpload" class="btn btn-primary">
                        <i class="fas fa-camera me-2"></i> Upload Images/Videos
                    </label>
                    <input type="file" id="fileUpload" name="media[]" multiple accept="image/*" style="display:none">
                </div>
                
                <!-- Preview Container - Initially hidden -->
                <div id="previewContainer" class="mb-3" style="display: none;">
                    <!-- Bootstrap Carousel with Navigation Arrows -->
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
                        <div class="carousel-inner"></div>
                    </div>
                </div>

                <!-- Visibility Dropdown -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-eye"></i>
                        </span>
                        <select class="form-select" id="visibility" name="visibility" required>
                            <?php foreach ($visibilityOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>">
                                    <?php echo ucfirst(htmlspecialchars($option)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a type="button" class="btn btn-secondary" href="/socialMedia/public/home">Cancel</a>
                    <button type="submit" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>