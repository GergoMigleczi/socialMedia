<?php 
namespace Components;

use Core\AssetManager;

class FriendCardComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addScript('friendCard-script', '/socialMedia/public/assets/js/components/friendCard.js');
    }
    
    public static function render($friend) {
        //self::init();
        
        // Chat rendering html
        include VIEWS_PATH .'/components/friendCard.php';
    }
}

?>