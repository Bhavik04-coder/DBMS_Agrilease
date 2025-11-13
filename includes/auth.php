<?php
require_once 'config.php';
require_once 'functions.php';


if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}


function getCurrentUser() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>
