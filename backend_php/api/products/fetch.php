<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/config.php';

try {
    // Build query with filters
    $query = "SELECT * FROM products WHERE status = 'available'";
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = mysqli_real_escape_string($conn, $_GET['category']);
        $query .= " AND category = '$category'";
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = mysqli_real_escape_string($conn, '%' . $_GET['search'] . '%');
        $query .= " AND (title LIKE '$search' OR description LIKE '$search' OR location LIKE '$search')";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT 100";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'count' => count($products),
        'products' => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
