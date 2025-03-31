<?php 
namespace App\Components;

class CommentComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
    }
    
    public static function renderComment($comment) {
        // Post rendering html
        include VIEWS_PATH .'/components/comment.php';
    }
}

?>