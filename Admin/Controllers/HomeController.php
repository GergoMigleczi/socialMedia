<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Post;

class HomeController extends Controller
{    
    private $logFile = 'adminHome.log';
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
        $this->denyIfNotAdmin();

        try{
            // Render the home view
            View::render('pages/home', [
                'title' => 'Admin Home'
            ], context: 'admin');
        }catch(\Exception $e){
            $this->logger->error("Controllers/Admin/HomeController->showHome(): " . $e->getMessage());
            $this->redirect('500');
        }
    }
}