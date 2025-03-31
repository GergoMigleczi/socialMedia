<?php
namespace App\Core;

class View
{
    /**
     * Render a view template with data
     *
     * @param string $view The view file path relative to Views directory
     * @param array $data Data to pass to the view
     * @param bool $showHeader Whether to show the header
     * @param string $context 'app' or 'admin' to determine which views directory to use
     * @param string $layout The layout file to use (defaults to 'main')
     * @return void
     */
    public static function render($view, $data = [], $showHeader = true, $context = 'app', $layout = 'main')
    {
        // Determine the views root path based on context
        if ($context === 'admin') {
            $viewsRoot = ADMIN_ROOT . '/Views';
            // If admin layout doesn't exist, fall back to app layout
            $layoutFile = file_exists(ADMIN_ROOT . '/Views/layouts/' . $layout . '.php') 
                ? ADMIN_ROOT . '/Views/layouts/' . $layout . '.php'
                : APP_ROOT . '/Views/layouts/' . $layout . '.php';
        } else {
            $viewsRoot = APP_ROOT . '/Views';
            $layoutFile = APP_ROOT . '/Views/layouts/' . $layout . '.php';
        }
        
        // Convert view path format (e.g., 'auth/login' to '/auth/login.php')
        $viewFile = $viewsRoot . '/' . str_replace('.', '/', $view) . '.php';
        
        // Check if view exists
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }
        
        // Extract data to make variables available in the view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewFile;
        
        // Get the content and stop buffering
        $content = ob_get_clean();

        // Check if layout exists
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: $layoutFile");
        }

        // Render the layout with the content
        include $layoutFile;
    }
}