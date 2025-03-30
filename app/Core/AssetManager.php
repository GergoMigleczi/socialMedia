<?php 
namespace Core;

class AssetManager {
    private static $styles = [];
    private static $scripts = [];
    
    public static function addStyle($name, $path, $rel = 'stylesheet') {
        if (!isset(self::$styles[$name])) {
            $style = [
                'path' => $path,
                'rel' => $rel
            ];
            self::$styles[$name] = $style;
        }
    }
    
    public static function addScript($name, $path, $type = 'module') {
        if (!isset(self::$scripts[$name])) {
            $script = [
                'path' => $path,
                'type' => $type
            ];
            self::$scripts[$name] = $script;
        }
    }
    
    public static function renderStyles() {
        foreach (self::$styles as $style) {
            echo '<link rel="'. $style['rel'] .'" href="' . htmlspecialchars($style['path']) . '">';
        }
    }
    
    public static function renderScripts() {
        foreach (self::$scripts as $script) {
            echo '<script src="' . htmlspecialchars($script['path']) . '" type="'.$script['type'] .'"></script>';
        }
    }
}
?>

