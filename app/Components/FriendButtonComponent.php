<?php 
namespace App\Components;

class FriendButtonComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
    }
    
    public static function render(int $profileId, string $status, bool $displayBool = true) {
        $display = '';
        if(!$displayBool){
            $display = 'none';
        }
        // Chat rendering html
        include VIEWS_PATH .'/components/friendButton.php';
    }
}

?>