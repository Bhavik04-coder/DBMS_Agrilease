# Payment System Guide - AgriLease

## Overview
The AgriLease payment system now supports split payments with deposit and final payment functionality, along with payment tracking and verification.

## Features

### 1. **Split Payment System**
- **Deposit Payment (30%)**: Required to confirm booking
- **Final Payment (70%)**: Due before rental period starts
- Automatic calculation based on total booking amount

### 2. **Payment Tracking**
- Track deposit and final payment status separately
- Payment history for each booking
- Owner verification of payments
- Automatic notifications for payment events

### 3. **Payment Methods**
- Manual payment recording (Cash, Bank Transfer, UPI, Card, Cheque)
- Payment gateway integration ready (Razorpay, Stripe)
- Transaction ID tracking
- Payment notes and references

### 4. **Owner Payment Management**
- View pending payments
- Mark payments as received
- Verify payment details
- Track payment history

## Database Changes

### New Tables

#### `payments` Table
Stores detailed payment records for each booking:
- `id`: Primary key
- `booking_id`: Reference to booking
- `payment_type`: deposit, final, or full
- `amount`: Payment amount
- `payment_method`: Payment method used
- `payment_gateway`: Gateway used (if online)
- `transaction_id`: Transaction reference
- `payment_status`: pending, processing, completed, failed, refunded
- `paid_by`: User who made payment
- `verified_by`: Owner who verified payment
- `payment_date`: When payment was made
- `verification_date`: When payment was verified
- `payment_proof`: File path for payment proof (optional)
- `notes`: Additional notes

### Updated Tables

#### `bookings` Table - New Columns
- `deposit_amount`: Calculated deposit (30%)
- `deposit_paid`: Boolean flag
- `deposit_paid_date`: Timestamp
- `final_amount`: Calculated final amount (70%)
- `final_paid`: Boolean flag
- `final_paid_date`: Timestamp
- `payment_gateway`: Gateway used
- `transaction_id`: Transaction reference

#### `receipts` Table - New Columns
- `deposit_amount`: Deposit amount
- `final_amount`: Final amount
- `deposit_paid`: Boolean flag
- `final_paid`: Boolean flag

## Installation

### Step 1: Setup Database
Run the complete SQL script (includes payment system):
```bash
mysql -u root -p < agrilease_complete.sql
```

Or import via phpMyAdmin:
1. Open phpMyAdmin
2. Click Import tab
3. Choose `agrilease_complete.sql`
4. Click Go

**Note:** This will create a fresh database with all payment features included.

### Step 2: Verify Installation
Check if new tables and columns exist:
```sql
SHOW TABLES LIKE 'payments';
DESCRIBE bookings;
DESCRIBE receipts;
```

## Usage

### For Renters

#### 1. Making a Booking
- Book equipment as usual
- System automatically calculates deposit (30%) and final amount (70%)
- Initial payment records are created

#### 2. Making Payments
- Go to "My Bookings"
- Click on a booking
- Click "Manage Payments" button
- Choose payment type (Deposit or Final)
- Record payment details:
  - Payment method
  - Transaction ID (optional)
  - Notes (optional)
- Submit for owner verification

#### 3. Tracking Payments
- View payment status on receipt
- See payment history
- Get notifications when payments are verified

### For Owners

#### 1. Viewing Payment Requests
- Go to "Manage Bookings"
- See pending payments highlighted
- Click "View Receipt" or "Manage Payments"

#### 2. Verifying Payments
- Review payment details
- Check transaction ID if provided
- Click "Confirm Payment Received"
- Renter gets notified automatically

#### 3. Payment Status
- Track which bookings have pending payments
- View complete payment history
- Monitor deposit and final payment status

## Payment Gateway Integration

### Razorpay Integration (Example)

#### 1. Get API Keys
- Sign up at https://razorpay.com
- Get Key ID and Key Secret from Dashboard

#### 2. Configure
Edit `payment_gateway.php`:
```php
define('RAZORPAY_KEY_ID', 'your_key_id_here');
define('RAZORPAY_KEY_SECRET', 'your_key_secret_here');
```

#### 3. Uncomment Integration Code
Uncomment the Razorpay script section at the bottom of `payment_gateway.php`

#### 4. Create Order Endpoint
Create `create_razorpay_order.php`:
```php
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$api_key = 'your_key_id';
$api_secret = 'your_key_secret';

$amount = $_POST['amount'] * 100; // Convert to paise

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $api_key . ':' . $api_secret);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => 'booking_' . $_POST['booking_id']
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
```

### Stripe Integration (Example)

#### 1. Install Stripe PHP Library
```bash
composer require stripe/stripe-php
```

#### 2. Configure
```php
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('your_secret_key');
```

#### 3. Create Payment Intent
```php
$intent = \Stripe\PaymentIntent::create([
    'amount' => $amount * 100,
    'currency' => 'inr',
    'metadata' => ['booking_id' => $booking_id]
]);
```

## Notifications

The system automatically sends notifications for:
- New payment recorded (to owner)
- Payment verified (to renter)
- Payment reminders (can be implemented)

## Security Considerations

1. **CSRF Protection**: All forms use CSRF tokens
2. **User Verification**: Only booking parties can access payment info
3. **SQL Injection**: All queries use prepared statements
4. **XSS Protection**: All output is sanitized

## Customization

### Change Payment Split Ratio
Edit the trigger in `agrilease_complete.sql` (line ~260):
```sql
SET NEW.deposit_amount = NEW.total_price * 0.30;  -- Change 0.30 to desired ratio
SET NEW.final_amount = NEW.total_price * 0.70;    -- Change 0.70 accordingly
```
Then re-import the database.

### Add Payment Proof Upload
1. Add file upload field in payment form
2. Handle file upload in `payment.php`
3. Store file path in `payment_proof` column
4. Display in payment history

### Add Payment Reminders
Create a cron job to send reminders:
```php
// payment_reminders.php
$pending = $pdo->query("
    SELECT * FROM bookings 
    WHERE deposit_paid = 0 
    AND DATEDIFF(start_date, NOW()) <= 7
")->fetchAll();

foreach ($pending as $booking) {
    sendNotification(
        $booking['renter_id'],
        'Payment Reminder',
        'Your deposit payment is pending...',
        'payment'
    );
}
```

## Troubleshooting

### Payments Not Showing
- Check if `payment_system_update.sql` was run successfully
- Verify `payments` table exists
- Check browser console for JavaScript errors

### Payment Status Not Updating
- Verify triggers are created: `SHOW TRIGGERS;`
- Check trigger execution: Look at booking status after payment
- Review error logs

### Gateway Integration Issues
- Verify API keys are correct
- Check if gateway is in test/live mode
- Review gateway documentation
- Check server PHP version compatibility

## API Endpoints (For Future Mobile App)

### Get Payment Status
```
GET /api/payment_status.php?booking_id=123
```

### Record Payment
```
POST /api/record_payment.php
{
    "booking_id": 123,
    "payment_type": "deposit",
    "amount": 1500,
    "payment_method": "upi",
    "transaction_id": "TXN123456"
}
```

### Verify Payment
```
POST /api/verify_payment.php
{
    "payment_id": 456,
    "verified_by": 789
}
```

## Support

For issues or questions:
1. Check this guide first
2. Review database logs
3. Check PHP error logs
4. Test with sample data

## Future Enhancements

- [ ] Automatic payment reminders
- [ ] Payment proof upload
- [ ] Refund management
- [ ] Payment analytics dashboard
- [ ] Multiple payment gateways
- [ ] Installment payments
- [ ] Wallet system
- [ ] Payment receipts via email
- [ ] SMS notifications for payments
- [ ] Payment dispute resolution

## License
Part of AgriLease project
