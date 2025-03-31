<?php

// Define application root
define('DIRECTORY_ROOT', dirname(__DIR__));
define('APP_ROOT', DIRECTORY_ROOT . '/App');
define('ADMIN_ROOT', DIRECTORY_ROOT . '/Admin');
define('PUBLIC_ROOT', DIRECTORY_ROOT . '/public');

// Load configuration
require_once APP_ROOT . '/Config/envLoader.php';
require_once APP_ROOT . '/Config/app.php';
require_once APP_ROOT . '/Config/databaseConfig.php';

// Autoload classes
// When a new class is called, PHP calls this function in the background
//      to find that class and import it
spl_autoload_register(function($className) {
    $classPath = str_replace('\\', '/', $className);
    
    // Check if it's an Admin namespace class
    if (strpos($className, 'Admin\\') === 0) {
        $file = ADMIN_ROOT . '/' . substr($classPath, 6) . '.php';
    }
    // Check if it's an App namespace class
    elseif (strpos($className, 'App\\') === 0) {
        $file = APP_ROOT . '/' . substr($classPath, 4) . '.php';
    }
    // For other classes (like Core), check in APP_ROOT
    else {
        $file = APP_ROOT . '/' . $classPath . '.php';
    }
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialise database
// require_once APP_ROOT . '/DatabaseInit/databaseMigration.php';

// Initialise router
$router = new App\Core\Router();

// Page Requests
$router->get('/home', 'Admin\Controllers\HomeController@showHome');

// Process the request
$router->dispatch();