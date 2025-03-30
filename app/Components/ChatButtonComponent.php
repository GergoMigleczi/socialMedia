<?php 
namespace Components;

use Core\AssetManager;

class ChatButtonComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addScript('chatButton-script', '/socialMedia/public/assets/js/components/chatButton.js');
    }
    
    public static function render(int $profileId, bool $displayBool = true) {
        $display = '';
        if(!$displayBool){
            $display = 'none';
        }
        
        // Chat rendering html
        include VIEWS_PATH .'/components/chatButton.php';
    }
}

?>