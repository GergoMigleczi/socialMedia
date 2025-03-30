<?php
use Core\AssetManager;
use Components\HeaderComponent;

HeaderComponent::init();

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'My Social Media App' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Common meta tags, CSS -->
    <?php AssetManager::renderStyles(); ?>
</head>
<body>
    <!-- Header -->
    <?php if ($showHeader): ?>
        <?php HeaderComponent::renderHeader() ?>
    <?php endif; ?>
    
    <!-- Main -->
    <main>
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer>
        <!-- Common footer -->
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/socialMedia/public/assets/js/pages/main.js" type="module"></script>
    <!-- Common JavaScript -->
    <?php AssetManager::renderScripts(); ?>
</body>
</html>