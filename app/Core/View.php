<?php
namespace Core;

class View
{
    /**
     * Render a view template with data
     *
     * @param string $view The view file path relative to Views directory
     * @param array $data Data to pass to the view
     * @return void
     */
    public static function render($view, $data = [], $showHeader = true)
    {
        // Convert view path format (e.g., 'auth/login' to '/auth/login.php')
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
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

        // Render the layout with the content
        include VIEWS_PATH . '/layouts/main.php';
        
    }
}