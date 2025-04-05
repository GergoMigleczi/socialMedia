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
    
    // Check if it's an App namespace class
    if (strpos($className, 'App\\') === 0) {
        $file = APP_ROOT . '/' . substr($classPath, 4) . '.php';
    }
    // Check if it's an Admin namespace class
    elseif (strpos($className, 'Admin\\') === 0) {
        $file = ADMIN_ROOT . '/' . substr($classPath, 6) . '.php';
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
//require_once APP_ROOT . '/DatabaseInit/databaseMigration.php';

// Initialise router
$router = new App\Core\Router();

// Page Requests
$router->get('/unauthorisedAccess', 'App\Controllers\AuthController@showUnAuth');
$router->get('/500', 'App\Controllers\ErrorController@show500');


$router->get('/', 'App\Controllers\AuthController@showLogin');
$router->get('/login', 'App\Controllers\AuthController@showLogin');
$router->get('/logout', 'App\Controllers\AuthController@logout');
$router->get('/register', 'App\Controllers\AuthController@showRegister');
$router->get('/home', 'App\Controllers\HomeController@showHome');

$router->get('/chats', 'App\Controllers\ChatController@showChats');
$router->get('/chats/{chatId}', 'App\Controllers\ChatController@showChat');

$router->get('/addPost', 'App\Controllers\PostController@showAddPost');

$router->get('/profile/{profileId}', 'App\Controllers\ProfileController@showProfile');
$router->get('/profile/{profileId}/friends', 'App\Controllers\FriendController@showFriends');

// API routes
$router->post('/api/auth/login', 'App\Controllers\AuthController@login');
$router->post('/api/auth/register', 'App\Controllers\AuthController@register');
$router->get('/getImage', 'App\Controllers\ImageController@getImage');

$router->post('/api/posts', 'App\Controllers\PostController@createPost');
$router->get('/api/posts/{postId}/comments', 'App\Controllers\CommentController@getComments');
$router->post('/api/posts/{postId}/comments', 'App\Controllers\CommentController@createComment');
$router->post('/api/posts/{postId}/likes', 'App\Controllers\LikeController@createLike');
$router->delete('/api/posts/{postId}/likes', 'App\Controllers\LikeController@deleteLike');

$router->get('/api/chats/private/{profileId}', 'App\Controllers\ChatController@getPrivateChat');
$router->post('/api/chats/private', 'App\Controllers\ChatController@createPrivateChat');
$router->post('/api/chats/{chatId}/messages', 'App\Controllers\ChatController@createMessage');

$router->post('/api/friends', 'App\Controllers\FriendController@handleFriendAction');
$router->get('/api/friends/{profileId}/isFriend', 'App\Controllers\FriendController@isFriend');


$router->post('/api/profiles/block', 'App\Controllers\BlockingController@blockProfile');
$router->post('/api/profiles/unblock', 'App\Controllers\BlockingController@unblockProfile');
$router->post('/api/profiles/report', 'App\Controllers\ProfileReportController@reportProfile');

// Process the request
$router->dispatch();