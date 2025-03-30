<?php 
namespace Components;
use Core\AssetManager;

class HeaderComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addStyle('profileComponent-style', '/socialMedia/public/assets/css/profileComponent.css');
    }
    
    public static function renderHeader() {
        // Post rendering html
        include VIEWS_PATH .'/components/header.php';
    }
}

?>