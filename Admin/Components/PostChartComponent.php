<?php 
namespace Admin\Components;

use App\Core\AssetManager;
class PostChartComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
        AssetManager::addScript('chartJS', "https://cdn.jsdelivr.net/npm/chart.js");
        AssetManager::addScript('chartjs-adapter', "https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns");
        AssetManager::addScript('post-chart-script', "/socialMedia/public/assets/adminjs/components/postChart.js");
    }
    
    public static function render($profileId) {
        
        // User Card rendering html
        include ADMIN_ROOT .'/Views/components/postChart.php';
    }
}

?>