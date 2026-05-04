<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agrilease_v2');


define('BASE_URL', 'http://localhost/agrilease');
define('UPLOAD_DIR', 'assets/images/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("ERROR: Could not connect to database. Please contact administrator.");
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
