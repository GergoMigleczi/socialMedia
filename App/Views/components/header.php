
<header class="bg-white border-bottom sticky-top">
    <div class="container-fluid px-3">
        <div class="row align-items-center py-2">
            <!-- Logo -->
            <div class="col-auto">
                <a class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center text-decoration-none"
                    style="width: 40px; height: 40px; font-size: 18px;" 
                    href="/socialMedia/public/home"
                >
                    SM
                </a>
            </div>
            
            <!-- Search Bar -->
            <div class="col">
                <input 
                    type="search" 
                    id="search-users" 
                    class="form-control" 
                    placeholder="Search for users..."
                >
            </div>
            
            <!-- Icons -->
            <div class="col-auto">
                <div class="d-flex align-items-center">
                    
                    <!-- Messages -->
                    <a 
                        id="messages-toggle" 
                        class="btn btn-outline-secondary border-0 me-2"
                        href="/socialMedia/public/chats"
                    >
                        <i class="bi bi-chat"></i>
                    </a>
                    
                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <button 
                            id="profile-dropdown" 
                            class="btn" 
                            data-bs-toggle="dropdown"
                        >
                            <img class="small-profile-picture" src="<?= base_url('/public/getImage?url=' . $_SESSION['profilePicture']) ?>" class="d-block" alt="Profile image">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/socialMedia/public/profile/<?=$_SESSION['profileId']?>">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if($isAdmin): ?>
                                <li><a class="dropdown-item" href="/socialMedia/admin/users">Admin Page</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/socialMedia/public/logout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>