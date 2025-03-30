<?php

// env_loader.php
$envFile = DIRECTORY_ROOT . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignore comments
        list($key, $value) = explode('=', $line, 2);
        putenv("$key=$value");
    }
}

?>