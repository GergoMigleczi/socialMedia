<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Models\Post;

class ErrorController extends Controller
{    
    public function __construct()
    {
        // Call parent constructor with specific log file
        parent::__construct('home.log');
    }

    public function show500()
    {
        // Render the login view
        View::render('errors/500', [
            'title' => 'Internal Server Error'
        ], false);
    }

}