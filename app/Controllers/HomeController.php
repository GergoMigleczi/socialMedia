<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Post;

class HomeController extends Controller
{    
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct('home.log');
    }

    public function showHome()
    {
        $this->requireAuth(true);

        $post = new Post();
        $posts = $post->getVisiblePosts($this->session->getProfileId());
        // Render the login view
        View::render('pages/home', [
            'title' => 'Home',
            'posts' => $posts
        ]);
    }

}