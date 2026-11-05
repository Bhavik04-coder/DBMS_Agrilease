<?php
/**
 * update_booking.php
 * Handles booking status updates from the dashboard "Confirm" button.
 * Delegates to manage_bookings.php logic and redirects back to dashboard.
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php?error=invalid_token');
    exit;
}

$booking_id = (int)($_POST['booking_id'] ?? 0);
$new_status  = $_POST['status'] ?? '';

$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

if (!$booking_id || !in_array($new_status, $valid_statuses)) {
    header('Location: dashboard.php?error=invalid_request');
    exit;
}

// Verify the booking belongs to the current user as owner
$stmt = $pdo->prepare("
    SELECT b.*, p.title as product_title, u.full_name as renter_name
    FROM bookings b
    LEFT JOIN products p ON p.id = b.product_id
    LEFT JOIN users u ON u.id = b.renter_id
    WHERE b.id = ? AND b.owner_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: dashboard.php?error=booking_not_found');
    exit;
}

// Update booking status
$update = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND owner_id = ?");
$update->execute([$new_status, $booking_id, $_SESSION['user_id']]);

// Sync product status based on new booking status
if ($new_status === 'confirmed') {
    $pdo->prepare("UPDATE products SET status = 'booked', availability = 'Rented' WHERE id = ?")
        ->execute([$booking['product_id']]);
} elseif (in_array($new_status, ['completed', 'cancelled'])) {
    $pdo->prepare("UPDATE products SET status = 'available', availability = 'Available' WHERE id = ?")
        ->execute([$booking['product_id']]);
}

// Notify the renter
$status_messages = [
    'confirmed'  => "Your booking for '{$booking['product_title']}' has been confirmed!",
    'completed'  => "Your booking for '{$booking['product_title']}' has been marked as completed.",
    'cancelled'  => "Your booking for '{$booking['product_title']}' has been cancelled by the owner.",
];

if (isset($status_messages[$new_status])) {
    sendNotification($booking['renter_id'], 'Booking Status Update', $status_messages[$new_status], 'booking');
}

// Redirect to booking_confirmed page on confirmation, otherwise back to dashboard
if ($new_status === 'confirmed') {
    header('Location: booking_confirmed.php?id=' . $booking_id);
} else {
    header('Location: dashboard.php?updated=1');
}
exit;
