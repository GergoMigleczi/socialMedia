<?php 
namespace Components;

use Core\AssetManager;

class ChatCardComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addStyle('card-style', '/socialMedia/public/assets/css/card.css');
    }
    
    public static function render($chat) {
        //self::init();
        
        // Chat rendering html
        include VIEWS_PATH .'/components/chatCard.php';
    }
}

?>