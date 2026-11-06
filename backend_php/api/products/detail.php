<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/config.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Product ID required');
    }

    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found'
        ]);
        return;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'product' => $product
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
