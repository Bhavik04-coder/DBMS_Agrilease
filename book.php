<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['product_id'])) {
    header('Location: dashboard.php'); exit;
}
$pid = (int)$_POST['product_id'];

// fetch product
$stmt = $pdo->prepare("SELECT p.*, u.id as owner_id, u.full_name as owner_name, u.email as owner_email FROM products p LEFT JOIN users u ON u.id = p.listed_by WHERE p.id = ?");
$stmt->execute([$pid]);
$p = $stmt->fetch();
if (!$p) { die('Product not found'); }
if ((int)$p['listed_by'] === (int)$_SESSION['user_id']) { die('You cannot book your own product.'); }

// create booking
$stmt = $pdo->prepare("INSERT INTO bookings (product_id, renter_id, owner_id, status, renter_lat, renter_lng, owner_lat, owner_lng, created_at) VALUES (?, ?, ?, 'confirmed', ?, ?, ?, ?, NOW())");
$renter = $_SESSION['user_id'];
// try to pull renter lat/lng from users table
$rs = $pdo->prepare("SELECT lat,lng FROM users WHERE id = ?");
$rs->execute([$renter]);
$rdata = $rs->fetch();
$rlat = $rdata['lat'] ?? null; $rlng = $rdata['lng'] ?? null;
$olat = $p['lat'] ?? null; $olng = $p['lng'] ?? null;
$stmt->execute([$pid, $renter, $p['owner_id'], $rlat, $rlng, $olat, $olng]);
$bid = $pdo->lastInsertId();

// mark product as booked
$u = $pdo->prepare("UPDATE products SET status='booked' WHERE id = ?");
$u->execute([$pid]);

// create notification for owner
$msg = 'Your product "' . $p['title'] . '" was booked (booking id: ' . $bid . ') by user id ' . $renter;
$stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
$stmt->execute([$p['owner_id'], $msg]);

// redirect to receipt
header('Location: receipt.php?id=' . $bid);
exit;
?>