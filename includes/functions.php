<?php
/**
 * Translate a key using the current language.
 * Falls back to the key itself if not found.
 */
function t($key, $replace = []) {
    $str = $GLOBALS['_lang'][$key] ?? $key;
    foreach ($replace as $search => $val) {
        $str = str_replace('{' . $search . '}', $val, $str);
    }
    return $str;
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function handleFileUpload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File is too large'];
    }

    // Validate file type by both MIME (using finfo) and extension
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_type = $finfo->file($file['tmp_name']);
    if (!in_array($file_type, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Invalid file extension'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = uniqid() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        chmod($destination, 0644);
        return ['success' => true, 'path' => $destination, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function formatPrice($price) {
    return '₹' . number_format((float)$price, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

function generateBookingId() {
    return 'BK' . date('Ymd') . rand(1000, 9999);
}

function sendNotification($userId, $title, $message, $type = 'general') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $message, $type]);
    } catch (PDOException $e) {
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}

function getUnreadNotificationCount($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . BASE_URL . '/' . ltrim($url, '/'));
    exit();
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
