<?php 
namespace Admin\Components;

use App\Core\AssetManager;

class UserCardComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
    }
    
    public static function render($profile) {
        
        // User Card rendering html
        include ADMIN_ROOT .'/Views/components/userCard.php';
    }
}

?>