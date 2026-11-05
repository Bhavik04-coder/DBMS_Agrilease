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


if (empty($start_date) || empty($end_date)) {
    die('Start date and end date are required.');
}

$start = new DateTime($start_date);
$end = new DateTime($end_date);
$today = new DateTime();

if ($start < $today || $end < $start) {
    die('Invalid date range. Start date must be today or later, and end date must be on or after start date.');
}


$duration = $start->diff($end)->days + 1;


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


if ($p['status'] !== 'available') {
    die('This product is currently not available for booking.');
}


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


$total_price = $duration * (float)$p['price'];


$rs = $pdo->prepare("SELECT lat, lng, full_name FROM users WHERE id = ?");
$rs->execute([$_SESSION['user_id']]);
$rdata = $rs->fetch();
$rlat = $rdata['lat'] ?? null; 
$rlng = $rdata['lng'] ?? null;
$renter_name = $rdata['full_name'] ?? $_SESSION['username'];


// Calculate deposit and final amounts
$deposit_amount = $total_price * 0.30;
$final_amount = $total_price * 0.70;

$stmt = $pdo->prepare("
    INSERT INTO bookings (
        product_id, renter_id, owner_id, status, start_date, end_date, 
        total_price, deposit_amount, final_amount, renter_lat, renter_lng, owner_lat, owner_lng, notes
    ) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $pid, 
    $_SESSION['user_id'], 
    $p['owner_id'], 
    $start_date, 
    $end_date, 
    $total_price,
    $deposit_amount,
    $final_amount,
    $rlat, 
    $rlng, 
    $p['owner_lat'], 
    $p['owner_lng'],
    $notes
]);

$bid = $pdo->lastInsertId();

// Create initial payment records
$pdo->prepare("
    INSERT INTO payments (booking_id, payment_type, amount, payment_status, paid_by)
    VALUES (?, 'deposit', ?, 'pending', ?)
")->execute([$bid, $deposit_amount, $_SESSION['user_id']]);

$pdo->prepare("
    INSERT INTO payments (booking_id, payment_type, amount, payment_status, paid_by)
    VALUES (?, 'final', ?, 'pending', ?)
")->execute([$bid, $final_amount, $_SESSION['user_id']]);


// Do NOT mark the product as booked yet — the owner must confirm first.
// The product status will be updated to 'booked' in update_booking.php / manage_bookings.php
// when the owner confirms the booking.


$owner_msg = "New booking request for your equipment '{$p['title']}' from {$renter_name}. Duration: {$duration} days. Total: ₹" . number_format($total_price, 2);
sendNotification($p['owner_id'], 'New Booking Request', $owner_msg, 'booking');

$renter_msg = "Your booking request for '{$p['title']}' has been submitted. You will be notified once the owner confirms.";
sendNotification($_SESSION['user_id'], 'Booking Request Submitted', $renter_msg, 'booking');


header('Location: receipt.php?id=' . $bid);
exit;
?>
