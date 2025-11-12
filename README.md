# AgriLease - Farm Equipment Rental Platform

## Setup

### 1. Import Database
```bash
mysql -u root -p < agrilease_complete.sql
```

### 2. Configure
Edit `includes/config.php`:
```php
define('DB_NAME', 'agrilease_v2');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Access
`http://localhost/agrilease/`

## Features

- ✅ Auto-generate receipts with GST
- ✅ Prevent double bookings
- ✅ Cancel bookings (renter)
- ✅ Confirm bookings (owner)
- ✅ Real-time price calculation
- ✅ Print/Download receipts

## How It Works

1. Renter books → Pending
2. Owner confirms → Receipt generated
3. View/Print receipt
4. Complete or Cancel
5. Product available again

## Files

- `agrilease_complete.sql` - Database
- `receipt.php` - Receipt viewer
- `booking_confirmed.php` - Confirmation page
- `manage_bookings.php` - Manage bookings
