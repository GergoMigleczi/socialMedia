<?php 

use Core\AssetManager;

// Add Page's styles and scripts
AssetManager::addScript('login-script', '/socialMedia/public/assets/js/pages/login.js');
AssetManager::addStyle('auth-style', '/socialMedia/public/assets/css/auth.css');

?>

<div class="h-100 bg-light d-flex align-items-center">
    <div class="container px-3">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 70px; height: 70px; font-size: 24px;">
                            SM
                        </div>
                        
                        <form id="login-form">
                            <div class="mb-2">
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="email" 
                                    name="email" 
                                    autocomplete="email"
                                    placeholder="Email" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="password" 
                                    name="password" 
                                    autocomplete="current-password"
                                    placeholder="Password" 
                                    required
                                >
                            </div>
                            
                            <button 
                                type="submit" 
                                id="login-button" 
                                class="btn btn-primary btn-lg w-100 mb-2"
                            >
                                Log In
                            </button>
                        </form>
                        
                        <a 
                            href="/socialMedia/public/register" 
                            id="create-account-button" 
                            class="btn btn-outline-primary btn-lg w-100"
                        >
                            Create New Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>