<?php
/**
 * Payment Gateway Integration
 * This file provides a basic structure for integrating payment gateways
 * Currently supports: Razorpay, Stripe, PayPal
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$payment_type = isset($_GET['type']) ? $_GET['type'] : 'deposit';

if (!$booking_id) {
    header('Location: my_bookings.php');
    exit;
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, p.title as product_title
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    WHERE b.id = ? AND b.renter_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found');
}

$amount = ($payment_type === 'deposit') ? $booking['deposit_amount'] : $booking['final_amount'];
$amount_paise = $amount * 100; // Convert to paise for Razorpay

// RAZORPAY CONFIGURATION (Example)
// Uncomment and add your keys when ready to use
// define('RAZORPAY_KEY_ID', 'your_key_id');
// define('RAZORPAY_KEY_SECRET', 'your_key_secret');

?>
<?php include 'includes/header.php'; ?>

<div class="container page">
  <div class="page-header">
    <h1 class="page-title">Complete Payment</h1>
    <p class="page-subtitle">Booking #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
  </div>

  <div class="payment-gateway-card">
    <div class="payment-info">
      <h3><?php echo htmlspecialchars($booking['product_title']); ?></h3>
      <div class="payment-details">
        <div class="detail-row">
          <span>Payment Type:</span>
          <strong><?php echo ucfirst($payment_type); ?> Payment</strong>
        </div>
        <div class="detail-row">
          <span>Amount to Pay:</span>
          <strong class="amount"><?php echo formatPrice($amount); ?></strong>
        </div>
      </div>
    </div>

    <div class="payment-methods">
      <h4>Choose Payment Method</h4>
      
      <!-- Manual Payment Option -->
      <div class="payment-method-card">
        <div class="method-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="method-details">
          <h5>Manual Payment</h5>
          <p>Pay via cash, bank transfer, or UPI and record the payment</p>
          <a href="payment.php?id=<?php echo $booking_id; ?>" class="btn btn-outline">Record Manual Payment</a>
        </div>
      </div>

      <!-- Razorpay Option (Example) -->
      <div class="payment-method-card">
        <div class="method-icon razorpay">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/>
            <path d="M4 9h16" stroke="white" stroke-width="2"/>
          </svg>
        </div>
        <div class="method-details">
          <h5>Online Payment (Razorpay)</h5>
          <p>Pay securely using credit/debit card, UPI, or net banking</p>
          <button class="btn btn-primary" onclick="alert('Razorpay integration coming soon! Please use manual payment for now.')">
            Pay with Razorpay
          </button>
        </div>
      </div>

      <!-- Stripe Option (Example) -->
      <div class="payment-method-card">
        <div class="method-icon stripe">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/>
          </svg>
        </div>
        <div class="method-details">
          <h5>Online Payment (Stripe)</h5>
          <p>International payments via Stripe</p>
          <button class="btn btn-primary" onclick="alert('Stripe integration coming soon! Please use manual payment for now.')">
            Pay with Stripe
          </button>
        </div>
      </div>
    </div>

    <div class="payment-note">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
      <div>
        <strong>Note:</strong> Online payment gateways require API keys to be configured. 
        For now, please use the manual payment option to record your payment.
      </div>
    </div>
  </div>

  <div class="action-buttons">
    <a href="receipt.php?id=<?php echo $booking_id; ?>" class="btn btn-outline">← Back to Receipt</a>
  </div>
</div>

<style>
.payment-gateway-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.payment-info {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.payment-info h3 {
  margin: 0 0 1rem 0;
  color: #1f2937;
}

.payment-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1rem;
}

.detail-row .amount {
  font-size: 1.5rem;
  color: #667eea;
}

.payment-methods h4 {
  margin: 0 0 1.5rem 0;
  color: #1f2937;
}

.payment-method-card {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 1rem;
  transition: all 0.2s;
}

.payment-method-card:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.method-icon {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 8px;
  color: #6b7280;
  flex-shrink: 0;
}

.method-icon.razorpay {
  background: #3395ff;
  color: white;
}

.method-icon.stripe {
  background: #635bff;
  color: white;
}

.method-details {
  flex: 1;
}

.method-details h5 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.method-details p {
  margin: 0 0 1rem 0;
  color: #6b7280;
  font-size: 0.875rem;
}

.payment-note {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  margin-top: 2rem;
  color: #1e40af;
}

.payment-note svg {
  flex-shrink: 0;
}

.action-buttons {
  display: flex;
  gap: 1rem;
}

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

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: transparent;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.btn-outline:hover {
  background: #f3f4f6;
}

@media (max-width: 768px) {
  .payment-method-card {
    flex-direction: column;
    text-align: center;
  }
  
  .method-icon {
    margin: 0 auto;
  }
}
</style>

<!-- Razorpay Integration Script (Uncomment when ready to use) -->
<!--
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function payWithRazorpay() {
  var options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
    "amount": "<?php echo $amount_paise; ?>",
    "currency": "INR",
    "name": "AgriLease",
    "description": "<?php echo ucfirst($payment_type); ?> Payment",
    "order_id": "", // Generate order_id from backend
    "handler": function (response){
      // Handle successful payment
      window.location.href = 'payment_success.php?booking_id=<?php echo $booking_id; ?>&payment_id=' + response.razorpay_payment_id;
    },
    "prefill": {
      "name": "<?php echo $_SESSION['username']; ?>",
      "email": "",
      "contact": ""
    },
    "theme": {
      "color": "#667eea"
    }
  };
  var rzp = new Razorpay(options);
  rzp.open();
}
</script>
-->

<?php include 'includes/footer.php'; ?>
