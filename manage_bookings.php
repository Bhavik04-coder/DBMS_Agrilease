<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Handle renter cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $booking_id = (int)$_POST['booking_id'];
        
        // Get booking details - verify renter owns this booking
        $stmt = $pdo->prepare("
            SELECT b.*, p.title as product_title, u.full_name as owner_name 
            FROM bookings b 
            LEFT JOIN products p ON p.id = b.product_id 
            LEFT JOIN users u ON u.id = b.owner_id 
            WHERE b.id = ? AND b.renter_id = ?
        ");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            $error = 'Booking not found or you do not have permission to cancel it.';
        } elseif ($booking['status'] === 'completed') {
            $error = 'Cannot cancel a completed booking.';
        } elseif ($booking['status'] === 'cancelled') {
            $error = 'This booking is already cancelled.';
        } else {
            // Update booking status to cancelled
            $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND renter_id = ?");
            $update_stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            // Make product available again
            $pdo->prepare("UPDATE products SET status = 'available', availability = 'Available' WHERE id = ?")
                ->execute([$booking['product_id']]);
            
            // Send notification to owner
            sendNotification($booking['owner_id'], 'Booking Cancelled', "The booking for '{$booking['product_title']}' has been cancelled by the renter.", 'booking');
            
            $success = 'Booking cancelled successfully. The product is now available for others to book.';
        }
    }
}

// Handle booking status updates (Owner actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $booking_id = (int)$_POST['booking_id'];
        $new_status = $_POST['status'];
        
        // Validate status
        $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            $error = 'Invalid status.';
        } else {
            // Get booking details
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
                $error = 'Booking not found.';
            } else {
                // Update booking status
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND owner_id = ?");
                $update_stmt->execute([$new_status, $booking_id, $_SESSION['user_id']]);
                
                // Update product status based on booking status
                if ($new_status === 'confirmed') {
                    $pdo->prepare("UPDATE products SET status = 'booked', availability = 'Rented' WHERE id = ?")
                        ->execute([$booking['product_id']]);
                } elseif (in_array($new_status, ['completed', 'cancelled'])) {
                    $pdo->prepare("UPDATE products SET status = 'available', availability = 'Available' WHERE id = ?")
                        ->execute([$booking['product_id']]);
                }
                
                // Send notification to renter
                $status_messages = [
                    'confirmed' => "Your booking for '{$booking['product_title']}' has been confirmed!",
                    'completed' => "Your booking for '{$booking['product_title']}' has been marked as completed.",
                    'cancelled' => "Your booking for '{$booking['product_title']}' has been cancelled."
                ];
                
                if (isset($status_messages[$new_status])) {
                    sendNotification($booking['renter_id'], 'Booking Status Update', $status_messages[$new_status], 'booking');
                }
                
                // Redirect to confirmation details page if confirmed
                if ($new_status === 'confirmed') {
                    $_SESSION['booking_confirmed'] = $booking_id;
                    header('Location: booking_confirmed.php?id=' . $booking_id);
                    exit;
                }
                
                $success = 'Booking status updated successfully.';
            }
        }
    }
}

// Get bookings received by the user (as owner)
$received_stmt = $pdo->prepare("
    SELECT b.*, p.title as product_title, p.image_path, u.full_name as renter_name, u.email as renter_email, u.phone as renter_phone
    FROM bookings b 
    LEFT JOIN products p ON p.id = b.product_id 
    LEFT JOIN users u ON u.id = b.renter_id 
    WHERE b.owner_id = ? 
    ORDER BY b.created_at DESC
");
$received_stmt->execute([$_SESSION['user_id']]);
$received_bookings = $received_stmt->fetchAll();

// Get bookings made by the user (as renter)
$made_stmt = $pdo->prepare("
    SELECT b.*, p.title as product_title, p.image_path, u.full_name as owner_name, u.email as owner_email, u.phone as owner_phone
    FROM bookings b 
    LEFT JOIN products p ON p.id = b.product_id 
    LEFT JOIN users u ON u.id = b.owner_id 
    WHERE b.renter_id = ? 
    ORDER BY b.created_at DESC
");
$made_stmt->execute([$_SESSION['user_id']]);
$made_bookings = $made_stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <h1 class="page-title">Manage Bookings</h1>
    <p class="page-subtitle">Handle booking requests and track your rental activity</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
      </svg>
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.154-.114l4-5.5z" clip-rule="evenodd" />
      </svg>
      <?php echo $success; ?>
      <?php if (isset($_SESSION['booking_confirmed'])): ?>
        <br><br>
        <a href="booking_confirmed.php?id=<?php echo $_SESSION['booking_confirmed']; ?>" class="btn btn-primary" style="margin-top:10px;">
          View Booking Details & Receipt
        </a>
        <?php unset($_SESSION['booking_confirmed']); ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="bookings-tabs">
    <button class="tab-button active" onclick="showTab('received')">
      Received Requests (<?php echo count($received_bookings); ?>)
    </button>
    <button class="tab-button" onclick="showTab('made')">
      My Bookings (<?php echo count($made_bookings); ?>)
    </button>
  </div>

  <!-- Received Bookings Tab -->
  <div id="received-tab" class="tab-content active">
    <div class="section-header">
      <h2>Booking Requests for Your Equipment</h2>
      <p>Manage requests from other users who want to rent your equipment</p>
    </div>

    <?php if (empty($received_bookings)): ?>
      <div class="empty-state">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h3>No booking requests yet</h3>
        <p>When users book your equipment, their requests will appear here</p>
        <a href="add_product.php" class="btn btn-primary">Add More Equipment</a>
      </div>
    <?php else: ?>
      <div class="bookings-grid">
        <?php foreach ($received_bookings as $booking): ?>
          <div class="booking-card">
            <div class="booking-image">
              <img src="<?php echo htmlspecialchars($booking['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
                   alt="<?php echo htmlspecialchars($booking['product_title']); ?>"
                   onerror="this.src='assets/images/placeholder.jpg'">
              <div class="booking-status status-<?php echo $booking['status']; ?>">
                <?php echo ucfirst($booking['status']); ?>
              </div>
            </div>

            <div class="booking-content">
              <div class="booking-header">
                <h3 class="booking-title"><?php echo htmlspecialchars($booking['product_title']); ?></h3>
                <div class="booking-id">ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
              </div>

              <div class="booking-details">
                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Renter:</strong> <?php echo htmlspecialchars($booking['renter_name']); ?></span>
                </div>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Period:</strong> 
                    <?php echo date('M j', strtotime($booking['start_date'])); ?> - 
                    <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                  </span>
                </div>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Total:</strong> <?php echo formatPrice($booking['total_price']); ?></span>
                </div>

                <?php if ($booking['notes']): ?>
                  <div class="detail-item notes">
                    <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?></span>
                  </div>
                <?php endif; ?>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Requested:</strong> <?php echo timeAgo($booking['created_at']); ?></span>
                </div>
              </div>

              <div class="booking-actions">
                <?php if ($booking['status'] === 'pending'): ?>
                  <form method="post" class="action-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="update_booking" value="1">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="btn btn-success btn-sm">
                      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      Confirm
                    </button>
                  </form>

                  <form method="post" class="action-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="update_booking" value="1">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this booking?')">
                      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                      Decline
                    </button>
                  </form>
                <?php elseif ($booking['status'] === 'confirmed'): ?>
                  <form method="post" class="action-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="update_booking" value="1">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.154-.114l4-5.5z" clip-rule="evenodd"/>
                      </svg>
                      Mark Complete
                    </button>
                  </form>
                <?php endif; ?>

                <a href="receipt.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline btn-sm">
                  <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                  </svg>
                  View Receipt
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Made Bookings Tab -->
  <div id="made-tab" class="tab-content">
    <div class="section-header">
      <h2>Your Booking Requests</h2>
      <p>Track the status of equipment you've requested to rent</p>
    </div>

    <?php if (empty($made_bookings)): ?>
      <div class="empty-state">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"></path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
          </svg>
        </div>
        <h3>No bookings made yet</h3>
        <p>Start browsing available equipment to make your first booking</p>
        <a href="products.php" class="btn btn-primary">Browse Equipment</a>
      </div>
    <?php else: ?>
      <div class="bookings-grid">
        <?php foreach ($made_bookings as $booking): ?>
          <div class="booking-card">
            <div class="booking-image">
              <img src="<?php echo htmlspecialchars($booking['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
                   alt="<?php echo htmlspecialchars($booking['product_title']); ?>"
                   onerror="this.src='assets/images/placeholder.jpg'">
              <div class="booking-status status-<?php echo $booking['status']; ?>">
                <?php echo ucfirst($booking['status']); ?>
              </div>
            </div>

            <div class="booking-content">
              <div class="booking-header">
                <h3 class="booking-title"><?php echo htmlspecialchars($booking['product_title']); ?></h3>
                <div class="booking-id">ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
              </div>

              <div class="booking-details">
                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Owner:</strong> <?php echo htmlspecialchars($booking['owner_name']); ?></span>
                </div>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Period:</strong> 
                    <?php echo date('M j', strtotime($booking['start_date'])); ?> - 
                    <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                  </span>
                </div>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Total:</strong> <?php echo formatPrice($booking['total_price']); ?></span>
                </div>

                <div class="detail-item">
                  <svg class="detail-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  <span><strong>Booked:</strong> <?php echo timeAgo($booking['created_at']); ?></span>
                </div>
              </div>

              <div class="booking-actions">
                <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                  <form method="post" class="action-form" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="cancel_booking" value="1">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                      Cancel Booking
                    </button>
                  </form>
                <?php endif; ?>
                
                <a href="receipt.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary btn-sm">
                  <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                  </svg>
                  View Receipt
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.bookings-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.tab-button {
  padding: 0.75rem 1.5rem;
  background: transparent;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab-button.active {
  color: #667eea;
  border-bottom-color: #667eea;
}

.tab-button:hover {
  color: #667eea;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.section-header {
  margin-bottom: 2rem;
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.section-header p {
  color: #6b7280;
  margin: 0;
}

.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.booking-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.booking-image {
  position: relative;
  height: 150px;
  overflow: hidden;
}

.booking-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.booking-status {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending {
  background: rgba(251, 191, 36, 0.9);
  color: #92400e;
}

.status-confirmed {
  background: rgba(34, 197, 94, 0.9);
  color: #14532d;
}

.status-completed {
  background: rgba(59, 130, 246, 0.9);
  color: #1e3a8a;
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.9);
  color: #991b1b;
}

.booking-content {
  padding: 1.5rem;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.booking-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  line-height: 1.3;
  flex: 1;
}

.booking-id {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  flex-shrink: 0;
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
}

.detail-item.notes {
  align-items: flex-start;
}

.detail-icon {
  flex-shrink: 0;
  color: #9ca3af;
  margin-top: 0.1rem;
}

.booking-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-form {
  display: inline-flex;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-1px);
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-outline {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.alert-error {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.alert-success {
  background-color: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.alert-icon {
  flex-shrink: 0;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.empty-icon {
  margin-bottom: 1.5rem;
  color: #d1d5db;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .bookings-grid {
    grid-template-columns: 1fr;
  }
  
  .booking-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .booking-actions {
    flex-direction: column;
  }
  
  .booking-actions .btn {
    justify-content: center;
  }
  
  .bookings-tabs {
    flex-direction: column;
  }
  
  .tab-button {
    text-align: left;
    border-bottom: none;
    border-left: 2px solid transparent;
    padding-left: 1rem;
  }
  
  .tab-button.active {
    border-left-color: #667eea;
    border-bottom-color: transparent;
  }
}
</style>

<script>
function showTab(tabName) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Remove active class from all buttons
  document.querySelectorAll('.tab-button').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected tab
  document.getElementById(tabName + '-tab').classList.add('active');
  
  // Add active class to clicked button
  event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>