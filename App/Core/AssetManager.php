<?php
namespace App\Core;

/**
 * Asset Manager for handling CSS and JavaScript assets
 * 
 * Provides centralized management of stylesheets and scripts with:
 * - Deduplication of assets
 * - Proper HTML escaping
 * - Flexible asset configuration
 * - Organized rendering
 */
class AssetManager {
    /**
     * Registered stylesheets
     * @var array<string, array{path: string, rel: string}>
     */
    private static $styles = [];
    
    /**
     * Registered JavaScript files
     * @var array<string, array{path: string, type: string}>
     */
    private static $scripts = [];
    
    /**
     * Add a stylesheet to the manager
     * 
     * @param string $name Unique identifier for the stylesheet
     * @param string $path Path/URL to the stylesheet
     * @param string $rel Relationship type (default: 'stylesheet')
     * @return void
     */
    public static function addStyle(string $name, string $path, string $rel = 'stylesheet'): void {
        // Only add if not already registered to prevent duplicates
        if (!isset(self::$styles[$name])) {
            self::$styles[$name] = [
                'path' => $path,
                'rel' => $rel
            ];
        }
    }
    
    /**
     * Add a script to the manager
     * 
     * @param string $name Unique identifier for the script
     * @param string $path Path/URL to the script
     * @param string $type Script type (default: 'module')
     * @return void
     */
    public static function addScript(string $name, string $path, string $type = 'module'): void {
        // Only add if not already registered to prevent duplicates
        if (!isset(self::$scripts[$name])) {
            self::$scripts[$name] = [
                'path' => $path,
                'type' => $type
            ];
        }
    }
    
    /**
     * Render all registered stylesheets as HTML link tags
     * 
     * @return void
     */
    public static function renderStyles(): void {
        foreach (self::$styles as $style) {
            // Output escaped HTML link tag
            echo '<link rel="' . htmlspecialchars($style['rel']) . 
                 '" href="' . htmlspecialchars($style['path']) . '">' . PHP_EOL;
        }
    }
    
    /**
     * Render all registered scripts as HTML script tags
     * 
     * @return void
     */
    public static function renderScripts(): void {
        foreach (self::$scripts as $script) {
            // Output escaped HTML script tag
            echo '<script src="' . htmlspecialchars($script['path']) . 
                 '" type="' . htmlspecialchars($script['type']) . '"></script>' . PHP_EOL;
        }
    }

    /**
     * Clear all registered styles
     * 
     * @return void
     */
    public static function clearStyles(): void {
        self::$styles = [];
    }

    /**
     * Clear all registered scripts
     * 
     * @return void
     */
    public static function clearScripts(): void {
        self::$scripts = [];
    }
}