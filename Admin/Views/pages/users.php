<?php 
// At the top of home.php, before any HTML output
use App\Components\ProfileComponent;
use App\Core\AssetManager;
use Admin\Components\UserCardComponent;
// Pre-initialize all components
ProfileComponent::init();

AssetManager::addScript('users-script', '/socialMedia/public/assets/adminjs/pages/users.js');
?>
    <div class="container-fluid px-3 mt-3 top" style="max-width: 900px;">
        <h1 class="mb-4">User Management</h1>
        
        <div class="row mb-3">
            <div class="col-md-8 my-1">
                <form id="users-search-form">
                    <div class="input-group">
                        <input id="users-search-input" type="text" class="form-control" placeholder="Search users..." value="<?=$search?>">
                        <button type="submit"  class="btn btn-outline-secondary" type="button">Search</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 my-1">
            <select class="form-select" id="users-sort-selection">
                <option value="name-ASC" <?php echo ($sort === 'name-ASC') ? 'selected' : ''; ?>>Sort by Name</option>
                <option value="posts-DESC" <?php echo ($sort === 'posts-DESC') ? 'selected' : ''; ?>>Sort by Posts (High to Low)</option>
                <option value="reports-DESC" <?php echo ($sort === 'reports-DESC') ? 'selected' : ''; ?>>Sort by Reports (High to Low)</option>
                <option value="id-ASC" <?php echo ($sort === 'id-ASC') ? 'selected' : ''; ?>>Sort by ID</option>
            </select>
            </div>
        </div>

        <?php if ((!empty($profileDTOs))): ?>
            <?php foreach ($profileDTOs as $profile): ?>
                <?php UserCardComponent::render($profile) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No users to display.</div>
        <?php endif; ?>
    </div>
