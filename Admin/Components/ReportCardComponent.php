<?php 
namespace Admin\Components;

class ReportCardComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
    }
    
    public static function render($report) {
        
        // User Card rendering html
        include ADMIN_ROOT .'/Views/components/reportCard.php';
    }
}

?>