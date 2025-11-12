<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['product_id'])) {
    header('Location: dashboard.php'); 
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Invalid session. Please refresh and try again.');
}

$pid = (int)$_POST['product_id'];
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$notes = sanitizeInput($_POST['notes'] ?? '');

// Validate dates
if (empty($start_date) || empty($end_date)) {
    die('Start date and end date are required.');
}

$start = new DateTime($start_date);
$end = new DateTime($end_date);
$today = new DateTime();

if ($start < $today || $end < $start) {
    die('Invalid date range.');
}

// Calculate duration and total price
$duration = $start->diff($end)->days + 1; // Include both start and end dates

// Fetch product
$stmt = $pdo->prepare("
    SELECT p.*, u.id as owner_id, u.full_name as owner_name, u.email as owner_email, u.lat as owner_lat, u.lng as owner_lng 
    FROM products p 
    LEFT JOIN users u ON u.id = p.listed_by 
    WHERE p.id = ?
");
$stmt->execute([$pid]);
$p = $stmt->fetch();

if (!$p) { 
    die('Product not found.'); 
}

if ((int)$p['listed_by'] === (int)$_SESSION['user_id']) { 
    die('You cannot book your own product.'); 
}

// Check if product is available
if ($p['status'] !== 'available') {
    die('This product is currently not available for booking.');
}

// Check for overlapping bookings
$overlap_check = $pdo->prepare("
    SELECT COUNT(*) FROM bookings 
    WHERE product_id = ? 
    AND status IN ('pending', 'confirmed')
    AND (
        (start_date <= ? AND end_date >= ?) OR
        (start_date <= ? AND end_date >= ?) OR
        (start_date >= ? AND end_date <= ?)
    )
");
$overlap_check->execute([$pid, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date]);

if ($overlap_check->fetchColumn() > 0) {
    die('This product is already booked for the selected dates. Please choose different dates.');
}

// Calculate total price
$total_price = $duration * (float)$p['price'];

// Get renter location
$rs = $pdo->prepare("SELECT lat, lng, full_name FROM users WHERE id = ?");
$rs->execute([$_SESSION['user_id']]);
$rdata = $rs->fetch();
$rlat = $rdata['lat'] ?? null; 
$rlng = $rdata['lng'] ?? null;
$renter_name = $rdata['full_name'] ?? $_SESSION['username'];

// Create booking
$stmt = $pdo->prepare("
    INSERT INTO bookings (
        product_id, renter_id, owner_id, status, start_date, end_date, 
        total_price, renter_lat, renter_lng, owner_lat, owner_lng, notes
    ) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $pid, 
    $_SESSION['user_id'], 
    $p['owner_id'], 
    $start_date, 
    $end_date, 
    $total_price,
    $rlat, 
    $rlng, 
    $p['owner_lat'], 
    $p['owner_lng'],
    $notes
]);

$bid = $pdo->lastInsertId();

// Update product status
$u = $pdo->prepare("UPDATE products SET status='booked', availability='Rented' WHERE id = ?");
$u->execute([$pid]);

// Send notifications
$owner_msg = "New booking request for your equipment '{$p['title']}' from {$renter_name}. Duration: {$duration} days. Total: ₹" . number_format($total_price, 2);
sendNotification($p['owner_id'], 'New Booking Request', $owner_msg, 'booking');

$renter_msg = "Your booking request for '{$p['title']}' has been submitted. You will be notified once the owner confirms.";
sendNotification($_SESSION['user_id'], 'Booking Request Submitted', $renter_msg, 'booking');

// Redirect to receipt
header('Location: receipt.php?id=' . $bid);
exit;
?>