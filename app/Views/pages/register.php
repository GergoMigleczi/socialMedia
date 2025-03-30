<?php 

use Core\AssetManager;
// Add Page's styles and scripts
AssetManager::addScript('register-script', '/socialMedia/public/assets/js/pages/register.js');
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
                        
                        <h2 class="h4 mb-4">Create an Account</h2>
                        
                        <form id="registration-form" enctype="multipart/form-data">
                            <div class="mb-2">
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="email" 
                                    name="email" 
                                    placeholder="Email" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="first-name" 
                                    name="firstName" 
                                    placeholder="First Name" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="last-name" 
                                    name="lastName" 
                                    placeholder="Last Name" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="date" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="date-of-birth" 
                                    name="dateOfBirth" 
                                    placeholder="Date of Birth" 
                                    required
                                >
                            </div>

                            <div class="mb-2">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg py-2 px-4" 
                                    id="password" 
                                    name="password" 
                                    autocomplete="new-password"
                                    placeholder="Password" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="file" 
                                    class="form-control form-control-lg py-2 px-4 d-none" 
                                    id="profile-picture" 
                                    name="profilePicture[]" 
                                    accept="image/*"
                                >
                                <div class="input-group">
                                    <label for="profile-picture" class="btn btn-secondary py-2 px-4 rounded-start d-flex align-items-center justify-content-center">
                                        Choose profile picture
                                    </label>
                                    <span id="file-name-display" class="form-control form-control-lg rounded-end border-start-0 text-muted"></span>
                                </div>
                            </div>
                            
                            <button 
                                type="submit" 
                                id="register-button" 
                                class="btn btn-primary btn-lg w-100 mb-2"
                            >
                                Register
                            </button>
                        </form>
                        
                        <a 
                            href="/socialMedia/public/login" 
                            id="login-link" 
                            class="btn btn-outline-primary btn-lg w-100"
                        >
                            Already have an account?
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>