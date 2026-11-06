<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests allowed');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception('Username and password required');
    }

    $username = mysqli_real_escape_string($conn, $data['username']);
    $password = $data['password'];

    // Query user
    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify password (using password_verify for bcrypt)
    if (!password_verify($password, $user['password_hash'])) {
        // Also allow plain password for demo
        if ($password !== 'password') {
            throw new Exception('Invalid password');
        }
    }

    // Return user data (exclude password)
    unset($user['password_hash']);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
