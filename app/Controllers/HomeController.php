<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Post;

class HomeController extends Controller
{    
    private $logFile = 'home.log';
    private $postModel;
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct($this->logFile);
        $this->postModel = new Post($this->logFile);
    }

    public function showHome()
    {
        $this->requireAuth(true);

        try{
            $posts = $this->postModel->getVisiblePosts($this->session->getProfileId());
            // Render the login view
            View::render('pages/home', [
                'title' => 'Home',
                'posts' => $posts
            ]);
        }catch(\Exception $e){
            $this->logger->error("Controllers/HomeController->showHome(): " . $e->getMessage());
            $this->redirect('500');
        }
    }
}