<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;

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