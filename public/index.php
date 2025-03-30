<?php

// Define application root
define('DIRECTORY_ROOT', dirname(__DIR__));
define('APP_ROOT', DIRECTORY_ROOT . '/app');
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
    $file = APP_ROOT . '/' . $classPath . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialise database
// require_once APP_ROOT . '/DatabaseInit/databaseMigration.php';

// Initialise router
$router = new Core\Router();

// Page Requests
$router->get('/unauthorisedAccess', 'Controllers\AuthController@showUnAuth');

$router->get('/', 'Controllers\AuthController@showLogin');
$router->get('/login', 'Controllers\AuthController@showLogin');
$router->get('/logout', 'Controllers\AuthController@logout');
$router->get('/register', 'Controllers\AuthController@showRegister');
$router->get('/home', 'Controllers\HomeController@showHome');

$router->get('/chats', 'Controllers\ChatController@showChats');
$router->get('/chats/{chatId}', 'Controllers\ChatController@showChat');

$router->get('/addPost', 'Controllers\PostController@showAddPost');

$router->get('/profile/{profileId}', 'Controllers\ProfileController@showProfile');
$router->get('/profile/{profileId}/friends', 'Controllers\FriendController@showFriends');

// API routes
$router->post('/api/auth/login', 'Controllers\AuthController@login');
$router->post('/api/auth/register', 'Controllers\AuthController@register');
$router->get('/getImage', 'Controllers\ImageController@getImage');

$router->post('/api/posts', 'PostController@createPost');
$router->get('/api/posts/{postId}/comments', 'Controllers\CommentController@getComments');
$router->post('/api/posts/{postId}/comments', 'Controllers\CommentController@createComment');
$router->post('/api/posts/{postId}/likes', 'Controllers\LikeController@createLike');
$router->delete('/api/posts/{postId}/likes', 'Controllers\LikeController@deleteLike');

$router->get('/api/chats/private/{profileId}', 'Controllers\ChatController@getPrivateChat');
$router->post('/api/chats/private', 'Controllers\ChatController@createPrivateChat');
$router->post('/api/chats/{chatId}/messages', 'Controllers\ChatController@createMessage');

$router->post('/api/friends', 'Controllers\FriendController@handleFriendAction');
$router->get('/api/friends/{profileId}/isFriend', 'Controllers\FriendController@isFriend');


$router->post('/api/profiles/block', 'Controllers\BlockingController@blockProfile');
$router->post('/api/profiles/unblock', 'Controllers\BlockingController@unblockProfile');

// Process the request
$router->dispatch();