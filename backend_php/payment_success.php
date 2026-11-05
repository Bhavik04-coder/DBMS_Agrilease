<?php
/**
 * payment_success.php
 * Handles the redirect after a successful online payment (e.g. Razorpay/Stripe).
 * Records the payment and redirects to the receipt page.
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$booking_id  = isset($_GET['booking_id'])  ? (int)$_GET['booking_id']              : 0;
$payment_id  = isset($_GET['payment_id'])  ? sanitizeInput($_GET['payment_id'])     : '';
$payment_type = isset($_GET['type'])       ? sanitizeInput($_GET['type'])           : 'deposit';

if (!$booking_id) {
    header('Location: my_bookings.php');
    exit;
}

// Verify the booking belongs to the current renter
$stmt = $pdo->prepare("
    SELECT b.*, p.title as product_title
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    WHERE b.id = ? AND b.renter_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

$amount = ($payment_type === 'deposit') ? $booking['deposit_amount'] : $booking['final_amount'];

// Record the payment as completed (online payments are auto-verified)
try {
    // Check if a pending payment record already exists for this type
    $existing = $pdo->prepare("
        SELECT id FROM payments
        WHERE booking_id = ? AND payment_type = ? AND payment_status = 'pending'
        ORDER BY created_at DESC LIMIT 1
    ");
    $existing->execute([$booking_id, $payment_type]);
    $existing_payment = $existing->fetch();

    if ($existing_payment) {
        // Update the existing pending record
        $pdo->prepare("
            UPDATE payments
            SET payment_status = 'completed',
                payment_method  = 'online',
                transaction_id  = ?,
                payment_date    = NOW(),
                verified_by     = ?,
                verification_date = NOW()
            WHERE id = ?
        ")->execute([$payment_id, $_SESSION['user_id'], $existing_payment['id']]);
    } else {
        // Insert a new completed payment record
        $pdo->prepare("
            INSERT INTO payments
                (booking_id, payment_type, amount, payment_method, transaction_id,
                 payment_status, paid_by, payment_date)
            VALUES (?, ?, ?, 'online', ?, 'completed', ?, NOW())
        ")->execute([$booking_id, $payment_type, $amount, $payment_id, $_SESSION['user_id']]);
    }

    // Update deposit_paid / final_paid flags on the booking
    if ($payment_type === 'deposit') {
        $pdo->prepare("UPDATE bookings SET deposit_paid = 1 WHERE id = ?")
            ->execute([$booking_id]);
    } else {
        $pdo->prepare("UPDATE bookings SET final_paid = 1 WHERE id = ?")
            ->execute([$booking_id]);
    }

    // Notify the owner
    sendNotification(
        $booking['owner_id'],
        'Payment Received',
        "Online {$payment_type} payment of ₹" . number_format($amount, 2) .
        " received for '{$booking['product_title']}'. Transaction ID: {$payment_id}",
        'payment'
    );

} catch (PDOException $e) {
    error_log("Payment success recording error: " . $e->getMessage());
}

// Redirect to receipt
header('Location: receipt.php?id=' . $booking_id . '&payment=success');
exit;
