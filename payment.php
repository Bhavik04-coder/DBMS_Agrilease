<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$error = '';
$success = '';
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    header('Location: my_bookings.php');
    exit;
}

// Get booking details with payment info
$stmt = $pdo->prepare("
    SELECT b.*, 
           p.title as product_title, 
           p.image_path,
           p.price as daily_price,
           owner.id as owner_id,
           owner.full_name as owner_name,
           owner.email as owner_email,
           owner.phone as owner_phone,
           renter.full_name as renter_name,
           renter.email as renter_email
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    LEFT JOIN users owner ON b.owner_id = owner.id
    LEFT JOIN users renter ON b.renter_id = renter.id
    WHERE b.id = ? AND (b.renter_id = ? OR b.owner_id = ?)
");
$stmt->execute([$booking_id, $_SESSION['user_id'], $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

$is_owner = ($booking['owner_id'] == $_SESSION['user_id']);
$is_renter = ($booking['renter_id'] == $_SESSION['user_id']);

// Get payment history
$payment_stmt = $pdo->prepare("
    SELECT p.*, u.full_name as paid_by_name, v.full_name as verified_by_name
    FROM payments p
    LEFT JOIN users u ON p.paid_by = u.id
    LEFT JOIN users v ON p.verified_by = v.id
    WHERE p.booking_id = ?
    ORDER BY p.created_at DESC
");
$payment_stmt->execute([$booking_id]);
$payments = $payment_stmt->fetchAll();

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        // Mark payment as received (owner only)
        if (isset($_POST['mark_payment_received']) && $is_owner) {
            $payment_id = (int)$_POST['payment_id'];
            $payment_type = $_POST['payment_type'];
            
            $update = $pdo->prepare("
                UPDATE payments 
                SET payment_status = 'completed', 
                    verified_by = ?,
                    verification_date = NOW(),
                    payment_date = NOW()
                WHERE id = ? AND booking_id = ?
            ");
            $update->execute([$_SESSION['user_id'], $payment_id, $booking_id]);
            
            sendNotification(
                $booking['renter_id'], 
                'Payment Confirmed', 
                "Your {$payment_type} payment for '{$booking['product_title']}' has been confirmed by the owner.",
                'payment'
            );
            
            $success = ucfirst($payment_type) . ' payment marked as received!';
            header("Refresh:0");
        }
        
        // Record manual payment (renter only)
        if (isset($_POST['record_payment']) && $is_renter) {
            $payment_type = $_POST['payment_type'];
            $payment_method = sanitizeInput($_POST['payment_method']);
            $transaction_id = sanitizeInput($_POST['transaction_id'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');
            
            $amount = ($payment_type === 'deposit') ? $booking['deposit_amount'] : $booking['final_amount'];
            
            $insert = $pdo->prepare("
                INSERT INTO payments (booking_id, payment_type, amount, payment_method, 
                                    transaction_id, payment_status, paid_by, notes, payment_date)
                VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
            ");
            $insert->execute([
                $booking_id, $payment_type, $amount, $payment_method, 
                $transaction_id, $_SESSION['user_id'], $notes
            ]);
            
            sendNotification(
                $booking['owner_id'], 
                'Payment Received', 
                "{$booking['renter_name']} has made a {$payment_type} payment for '{$booking['product_title']}'. Please verify.",
                'payment'
            );
            
            $success = 'Payment recorded! Waiting for owner verification.';
            header("Refresh:0");
        }
    }
}

// Calculate payment status
$deposit_payment = array_filter($payments, fn($p) => $p['payment_type'] === 'deposit' && $p['payment_status'] === 'completed');
$final_payment = array_filter($payments, fn($p) => $p['payment_type'] === 'final' && $p['payment_status'] === 'completed');
$pending_payments = array_filter($payments, fn($p) => $p['payment_status'] === 'pending');

$deposit_completed = !empty($deposit_payment) || $booking['deposit_paid'];
$final_completed = !empty($final_payment) || $booking['final_paid'];
?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <h1 class="page-title">Payment Management</h1>
    <p class="page-subtitle">Booking #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?> - <?php echo htmlspecialchars($booking['product_title']); ?></p>
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
    </div>
  <?php endif; ?>

  <!-- Payment Summary Card -->
  <div class="payment-summary-card">
    <div class="summary-header">
      <h2>Payment Summary</h2>
      <div class="booking-status status-<?php echo $booking['status']; ?>">
        <?php echo ucfirst($booking['status']); ?>
      </div>
    </div>

    <div class="payment-breakdown">
      <div class="breakdown-item">
        <span class="label">Total Amount:</span>
        <span class="value total"><?php echo formatPrice($booking['total_price']); ?></span>
      </div>
      
      <div class="breakdown-divider"></div>
      
      <div class="breakdown-item">
        <span class="label">Deposit (30%):</span>
        <span class="value"><?php echo formatPrice($booking['deposit_amount']); ?></span>
        <?php if ($deposit_completed): ?>
          <span class="status-badge paid">✓ Paid</span>
        <?php else: ?>
          <span class="status-badge pending">Pending</span>
        <?php endif; ?>
      </div>
      
      <div class="breakdown-item">
        <span class="label">Final Payment (70%):</span>
        <span class="value"><?php echo formatPrice($booking['final_amount']); ?></span>
        <?php if ($final_completed): ?>
          <span class="status-badge paid">✓ Paid</span>
        <?php else: ?>
          <span class="status-badge pending">Pending</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="payment-progress">
      <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo ($deposit_completed && $final_completed) ? '100' : ($deposit_completed ? '50' : '0'); ?>%"></div>
      </div>
      <div class="progress-labels">
        <span>Deposit</span>
        <span>Final Payment</span>
      </div>
    </div>
  </div>

  <!-- Payment Actions -->
  <?php if ($is_renter && $booking['status'] !== 'cancelled'): ?>
    <div class="payment-actions-card">
      <h3>Make Payment</h3>
      
      <?php if (!$deposit_completed): ?>
        <div class="payment-option">
          <div class="option-header">
            <h4>Pay Deposit (30%)</h4>
            <span class="amount"><?php echo formatPrice($booking['deposit_amount']); ?></span>
          </div>
          <p>Pay the deposit to confirm your booking</p>
          
          <button class="btn btn-primary" onclick="showPaymentForm('deposit')">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
              <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
            Pay Deposit
          </button>
        </div>
      <?php endif; ?>
      
      <?php if ($deposit_completed && !$final_completed): ?>
        <div class="payment-option">
          <div class="option-header">
            <h4>Pay Final Amount (70%)</h4>
            <span class="amount"><?php echo formatPrice($booking['final_amount']); ?></span>
          </div>
          <p>Complete your payment before the rental period</p>
          
          <button class="btn btn-primary" onclick="showPaymentForm('final')">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
              <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
            Pay Final Amount
          </button>
        </div>
      <?php endif; ?>
      
      <?php if ($deposit_completed && $final_completed): ?>
        <div class="payment-complete">
          <svg width="48" height="48" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.154-.114l4-5.5z" clip-rule="evenodd"/>
          </svg>
          <h4>Payment Complete!</h4>
          <p>All payments have been completed for this booking.</p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Payment History -->
  <?php if (!empty($payments)): ?>
    <div class="payment-history-card">
      <h3>Payment History</h3>
      
      <div class="payment-list">
        <?php foreach ($payments as $payment): ?>
          <div class="payment-item status-<?php echo $payment['payment_status']; ?>">
            <div class="payment-icon">
              <?php if ($payment['payment_status'] === 'completed'): ?>
                <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.154-.114l4-5.5z" clip-rule="evenodd"/>
                </svg>
              <?php else: ?>
                <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
              <?php endif; ?>
            </div>
            
            <div class="payment-details">
              <div class="payment-header">
                <h4><?php echo ucfirst($payment['payment_type']); ?> Payment</h4>
                <span class="payment-amount"><?php echo formatPrice($payment['amount']); ?></span>
              </div>
              
              <div class="payment-meta">
                <span>Method: <?php echo htmlspecialchars($payment['payment_method'] ?? 'Not specified'); ?></span>
                <?php if ($payment['transaction_id']): ?>
                  <span>Transaction ID: <?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                <?php endif; ?>
                <span>Date: <?php echo date('M j, Y g:i A', strtotime($payment['created_at'])); ?></span>
              </div>
              
              <?php if ($payment['notes']): ?>
                <p class="payment-notes"><?php echo htmlspecialchars($payment['notes']); ?></p>
              <?php endif; ?>
              
              <div class="payment-status-badge status-<?php echo $payment['payment_status']; ?>">
                <?php echo ucfirst($payment['payment_status']); ?>
              </div>
            </div>
            
            <?php if ($is_owner && $payment['payment_status'] === 'pending'): ?>
              <div class="payment-actions">
                <form method="post" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                  <input type="hidden" name="mark_payment_received" value="1">
                  <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                  <input type="hidden" name="payment_type" value="<?php echo $payment['payment_type']; ?>">
                  <button type="submit" class="btn btn-success btn-sm">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Confirm Payment Received
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Pending Payments Alert for Owner -->
  <?php if ($is_owner && !empty($pending_payments)): ?>
    <div class="alert alert-warning">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      You have <?php echo count($pending_payments); ?> pending payment(s) to verify.
    </div>
  <?php endif; ?>

  <div class="action-buttons">
    <a href="receipt.php?id=<?php echo $booking_id; ?>" class="btn btn-outline">
      <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
      </svg>
      View Receipt
    </a>
    <a href="<?php echo $is_owner ? 'manage_bookings.php' : 'my_bookings.php'; ?>" class="btn btn-outline">
      ← Back to Bookings
    </a>
  </div>
</div>

<!-- Payment Form Modal -->
<div id="paymentModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">Record Payment</h3>
      <button class="modal-close" onclick="closePaymentForm()">&times;</button>
    </div>
    
    <form method="post" class="payment-form">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" name="record_payment" value="1">
      <input type="hidden" name="payment_type" id="payment_type" value="">
      
      <div class="form-group">
        <label>Amount</label>
        <input type="text" id="payment_amount" class="form-control" readonly>
      </div>
      
      <div class="form-group">
        <label>Payment Method *</label>
        <select name="payment_method" class="form-control" required>
          <option value="">Select payment method</option>
          <option value="cash">Cash</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="upi">UPI</option>
          <option value="card">Credit/Debit Card</option>
          <option value="cheque">Cheque</option>
        </select>
      </div>
      
      <div class="form-group">
        <label>Transaction ID / Reference Number</label>
        <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
      </div>
      
      <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Add any additional notes"></textarea>
      </div>
      
      <div class="alert alert-info">
        <svg class="alert-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        Your payment will be pending until the owner verifies it.
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" onclick="closePaymentForm()">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit Payment</button>
      </div>
    </form>
  </div>
</div>

<style>
.payment-summary-card, .payment-actions-card, .payment-history-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.summary-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #1f2937;
}

.booking-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #d1fae5; color: #065f46; }
.status-completed { background: #e0e7ff; color: #3730a3; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.payment-breakdown {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
}

.breakdown-item .label {
  font-weight: 500;
  color: #6b7280;
}

.breakdown-item .value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.breakdown-item .value.total {
  font-size: 1.5rem;
  color: #667eea;
}

.breakdown-divider {
  height: 1px;
  background: #e5e7eb;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.paid {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.payment-progress {
  margin-top: 1.5rem;
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s ease;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: #6b7280;
}

.payment-option {
  padding: 1.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.option-header h4 {
  margin: 0;
  color: #1f2937;
}

.option-header .amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
}

.payment-option p {
  color: #6b7280;
  margin: 0 0 1rem 0;
}

.payment-complete {
  text-align: center;
  padding: 2rem;
  color: #10b981;
}

.payment-complete svg {
  margin-bottom: 1rem;
}

.payment-complete h4 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.payment-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-item {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.payment-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.payment-item.status-completed .payment-icon {
  background: #d1fae5;
  color: #065f46;
}

.payment-item.status-pending .payment-icon {
  background: #fef3c7;
  color: #92400e;
}

.payment-details {
  flex: 1;
}

.payment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.payment-header h4 {
  margin: 0;
  color: #1f2937;
}

.payment-amount {
  font-size: 1.125rem;
  font-weight: 700;
  color: #667eea;
}

.payment-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.payment-notes {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0.5rem 0;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
}

.payment-status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-top: 0.5rem;
}

.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  align-items: center;
  justify-content: center;
}

.modal.active {
  display: flex;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
  cursor: pointer;
  line-height: 1;
}

.payment-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.alert-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.alert-info { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

.alert-icon { flex-shrink: 0; }

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
.btn-success { background: #10b981; color: white; }
.btn-success:hover { background: #059669; }
.btn-outline { background: transparent; color: #6b7280; border: 2px solid #e5e7eb; }
.btn-outline:hover { background: #f3f4f6; }
.btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }

@media (max-width: 768px) {
  .payment-summary-card, .payment-actions-card, .payment-history-card {
    padding: 1rem;
  }
  
  .payment-item {
    flex-direction: column;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>

<script>
const depositAmount = <?php echo $booking['deposit_amount']; ?>;
const finalAmount = <?php echo $booking['final_amount']; ?>;

function showPaymentForm(type) {
  const modal = document.getElementById('paymentModal');
  const title = document.getElementById('modalTitle');
  const amountField = document.getElementById('payment_amount');
  const typeField = document.getElementById('payment_type');
  
  if (type === 'deposit') {
    title.textContent = 'Pay Deposit (30%)';
    amountField.value = '₹' + depositAmount.toFixed(2);
    typeField.value = 'deposit';
  } else {
    title.textContent = 'Pay Final Amount (70%)';
    amountField.value = '₹' + finalAmount.toFixed(2);
    typeField.value = 'final';
  }
  
  modal.classList.add('active');
}

function closePaymentForm() {
  document.getElementById('paymentModal').classList.remove('active');
}

// Close modal when clicking outside
document.getElementById('paymentModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closePaymentForm();
  }
});
</script>

<?php include 'includes/footer.php'; ?>
