# AgriLease - Agricultural Equipment Rental Platform

A comprehensive web-based platform for renting and listing agricultural equipment with integrated payment system.

## 🚀 Quick Start

### Installation (3 Steps)

**Step 1: Import Database**
```bash
mysql -u root -p < agrilease_complete.sql
```
Or use phpMyAdmin to import `agrilease_complete.sql`

**Step 2: Configure Database**
Edit `includes/config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agrilease_v2');
```

**Step 3: Access Application**
```
http://localhost/agrilease
```

## ✨ Features

### Core Features
- 🔐 User Authentication (Login/Register)
- 📦 Product Listing & Management
- 🔍 Search & Filter Equipment
- 📍 Location-based Search (GPS)
- 📅 Booking System
- 💰 Split Payment System (30% Deposit + 70% Final)
- 🧾 Receipt Generation
- 🔔 Notifications
- ⭐ Reviews & Ratings
- 👤 User Profiles

### Payment System
- **Split Payments**: 30% deposit to confirm, 70% final payment
- **Payment Tracking**: Complete payment history
- **Owner Verification**: Owners verify received payments
- **Multiple Methods**: Cash, UPI, Bank Transfer, Card, Cheque
- **Payment Gateway Ready**: Razorpay/Stripe integration template
- **Automatic Notifications**: Both parties notified of payment events
- **Transaction IDs**: Track all payment references

## 📁 Project Structure

```
agrilease/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── includes/
│   ├── auth.php          # Authentication middleware
│   ├── config.php        # Database configuration
│   ├── functions.php     # Helper functions
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
├── index.php             # Login page
├── register.php          # Registration page
├── dashboard.php         # Main dashboard
├── products.php          # Browse products
├── product_detail.php    # Product details
├── add_product.php       # Add new product
├── edit_product.php      # Edit product
├── my_products.php       # User's products
├── book.php              # Booking handler
├── my_bookings.php       # User's bookings
├── manage_bookings.php   # Manage bookings (owner)
├── payment.php           # Payment management
├── payment_gateway.php   # Payment gateway integration
├── receipt.php           # Booking receipt
├── profile.php           # User profile
├── notifications.php     # Notifications
└── agrilease_complete.sql # Database schema
```

## 🗄️ Database Schema

### Main Tables
- **users** - User accounts
- **products** - Equipment listings
- **bookings** - Rental bookings with payment tracking
- **payments** - Detailed payment transactions
- **receipts** - Booking receipts/invoices
- **notifications** - User notifications
- **reviews** - Product and user reviews

### Key Features
- Automatic payment calculation (30/70 split)
- Triggers for payment status updates
- Views for booking and payment summaries
- Stored procedures for common queries

## 💳 Payment System

### How It Works

**For Renters:**
1. Book equipment
2. System calculates deposit (30%) and final amount (70%)
3. Go to "Manage Payments"
4. Record deposit payment
5. Wait for owner verification
6. Record final payment
7. Complete!

**For Owners:**
1. Receive booking notification
2. Renter records payment
3. Get notification
4. Verify payment received
5. Renter gets confirmation

### Payment Methods
- Manual: Cash, UPI, Bank Transfer, Card, Cheque
- Online: Razorpay, Stripe (template ready)

### Customization

**Change Payment Split:**
Edit `agrilease_complete.sql` (line ~260):
```sql
SET NEW.deposit_amount = NEW.total_price * 0.30;  -- Change to 0.50 for 50%
SET NEW.final_amount = NEW.total_price * 0.70;    -- Change to 0.50 for 50%
```

## 🔧 Configuration

### Payment Gateway Integration

**Razorpay:**
1. Get API keys from https://razorpay.com
2. Edit `payment_gateway.php`:
```php
define('RAZORPAY_KEY_ID', 'your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_key_secret');
```
3. Uncomment integration code

**Stripe:**
1. Install: `composer require stripe/stripe-php`
2. Add API keys
3. Update payment_gateway.php

## 📱 Pages Overview

### Public Pages
- `/index.php` - Login
- `/register.php` - Registration

### User Pages
- `/dashboard.php` - Main dashboard
- `/products.php` - Browse equipment
- `/product_detail.php?id=X` - Product details
- `/my_bookings.php` - User's bookings
- `/profile.php` - User profile

### Owner Pages
- `/add_product.php` - List new equipment
- `/edit_product.php?id=X` - Edit listing
- `/my_products.php` - Manage listings
- `/manage_bookings.php` - Handle booking requests

### Payment Pages
- `/payment.php?id=X` - Payment management
- `/payment_gateway.php` - Gateway integration
- `/receipt.php?id=X` - Booking receipt

## 🔒 Security Features

- ✅ CSRF token protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output sanitization)
- ✅ Password hashing (bcrypt)
- ✅ Session management
- ✅ User authentication middleware
- ✅ Owner verification for payments

## 🎨 Frontend Features

- Responsive design (mobile-friendly)
- Modern UI with gradient themes
- Interactive forms with validation
- Image upload with preview
- GPS location detection
- Real-time notifications
- Smooth animations and transitions

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Maps**: Leaflet.js
- **Icons**: SVG icons
- **Styling**: Custom CSS with gradients

## 📊 Key Features Explained

### Booking System
- Date range selection
- Automatic price calculation
- Overlap prevention
- Status tracking (pending, confirmed, completed, cancelled)

### Payment Tracking
- Split payment support
- Payment history
- Owner verification
- Transaction ID tracking
- Multiple payment methods

### Notifications
- Booking requests
- Payment updates
- Status changes
- Real-time updates

### Location Features
- GPS coordinates
- Distance calculation
- Map visualization
- Location-based search

## 🐛 Troubleshooting

**Database Connection Error:**
- Check `includes/config.php` settings
- Verify MySQL is running
- Check database name and credentials

**Payments Not Showing:**
- Verify `agrilease_complete.sql` was imported
- Check if `payments` table exists
- Clear browser cache

**Images Not Uploading:**
- Check `assets/images/` folder permissions
- Verify file size limits in `php.ini`
- Check allowed file types

**Location Not Working:**
- Enable location permissions in browser
- Use HTTPS for production
- Check browser compatibility

## 📈 Future Enhancements

- [ ] Payment proof upload
- [ ] Automatic payment reminders
- [ ] Refund management
- [ ] Payment analytics dashboard
- [ ] Mobile app (React Native)
- [ ] SMS notifications
- [ ] Multi-language support
- [ ] Advanced search filters
- [ ] Equipment availability calendar
- [ ] Insurance integration

## 📝 License

This project is for educational purposes.

## 👥 Support

For issues or questions:
1. Check this README
2. Review `PAYMENT_SYSTEM_GUIDE.md` for payment details
3. Check database logs
4. Review PHP error logs

## 🎯 Default Credentials

After importing the database, you can use these sample accounts:
- Username: `john_farmer` / Password: `password`
- Username: `mary_agri` / Password: `password`

## 📞 Contact

For support or contributions, please refer to the project documentation.

---

**Version**: 2.0 with Payment System  
**Last Updated**: November 2024  
**Status**: Production Ready ✅
