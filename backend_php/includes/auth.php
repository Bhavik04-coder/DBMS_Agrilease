<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/functions.php';
}

if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('index.php');
}

function getCurrentUser() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}
?>
