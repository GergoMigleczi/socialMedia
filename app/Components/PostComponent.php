<?php 
namespace App\Components;

use App\Core\AssetManager;

class PostComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addStyle('post-style', '/socialMedia/public/assets/css/post.css');
        AssetManager::addStyle('carousel-style', '/socialMedia/public/assets/css/carousel.css');
        AssetManager::addStyle('comment-style', '/socialMedia/public/assets/css/comment.css');

        AssetManager::addScript('comment-script', '/socialMedia/public/assets/js/components/comment.js');
        AssetManager::addScript('like-script', '/socialMedia/public/assets/js/components/like.js');
        AssetManager::addScript('carousel-script', '/socialMedia/public/assets/js/components/carousel.js');

    }
    
    public static function render($post) {
        self::init();
        
        // Post rendering html
        include VIEWS_PATH .'/components/post.php';
    }
}

?>