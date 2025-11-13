# 🌾 AgriLease - Agricultural Equipment Rental Platform

A comprehensive web-based platform for renting and managing agricultural equipment. Built with PHP, MySQL, and modern web technologies.

## 📋 Features

### User Management
- User registration and authentication
- Profile management with location tracking
- Password change functionality
- Profile image upload

### Product Management
- Add, edit, and delete equipment listings
- Product categorization
- Image upload for products
- Location-based listings with GPS coordinates
- Product status management (Available/Booked/Maintenance)
- Cancel and confirm product listings
- Active bookings count display

### Booking System
- Browse available equipment
- Search and filter products by category, location, and price
- Date-based booking with overlap detection
- Booking confirmation and cancellation
- Automatic receipt generation
- Booking status tracking (Pending/Confirmed/Completed/Cancelled)
- Manage received and made bookings

### Notifications
- Real-time notification system
- Booking status updates
- Unread notification badges
- Notification types: Booking, Payment, System

### Receipt & Documentation
- Automatic receipt generation with GST calculation
- PDF-ready receipt format
- Booking details with map visualization
- Print-friendly design

### Reviews & Ratings
- Product review system
- 5-star rating system
- Verified reviews from completed bookings

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Maps:** Leaflet.js with OpenStreetMap
- **Icons:** SVG icons
- **Security:** CSRF protection, password hashing, SQL injection prevention

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/agrilease.git
   cd agrilease
   ```

2. **Import the database**
   - Open phpMyAdmin or MySQL command line
   - Import `agrilease_complete.sql`
   ```bash
   mysql -u root -p < agrilease_complete.sql
   ```

3. **Configure the application**
   - Edit `includes/config.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'agrilease_v2');
   define('BASE_URL', 'http://localhost/agrilease');
   ```

4. **Set permissions**
   ```bash
   chmod 755 assets/images/
   ```

5. **Access the application**
   - Open your browser
   - Navigate to `http://localhost/agrilease`

## 📁 Project Structure

```
agrilease/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── images/                # Product images
├── includes/
│   ├── config.php             # Database configuration
│   ├── auth.php               # Authentication middleware
│   ├── functions.php          # Helper functions
│   ├── header.php             # Header template
│   └── footer.php             # Footer template
├── index.php                  # Login page
├── register.php               # Registration page
├── dashboard.php              # Main dashboard
├── products.php               # Browse products
├── product_detail.php         # Product details
├── add_product.php            # Add new product
├── edit_product.php           # Edit product
├── my_products.php            # User's product listings
├── book.php                   # Booking handler
├── manage_bookings.php        # Manage bookings
├── my_bookings.php            # User's bookings
├── booking_confirmed.php      # Booking confirmation
├── receipt.php                # Receipt generation
├── profile.php                # User profile
├── notifications.php          # Notifications center
├── logout.php                 # Logout handler
└── agrilease_complete.sql     # Database schema
```

## 🔐 Security Features

- **CSRF Protection:** All forms include CSRF tokens
- **SQL Injection Prevention:** Prepared statements for all queries
- **XSS Prevention:** Input sanitization and output escaping
- **Password Security:** Bcrypt password hashing
- **Session Management:** Secure session handling
- **Ownership Verification:** Users can only modify their own data

## 🎨 Key Features Explained

### Product Cancel & Confirm
- **Cancel:** Marks product as unavailable, cancels all active bookings, notifies renters
- **Confirm:** Reactivates cancelled products, makes them available for booking

### Booking Management
- **Owner View:** Confirm, decline, or complete booking requests
- **Renter View:** Cancel bookings, view receipts
- **Status Tracking:** Real-time booking status updates

### Smart Navigation
- Dashboard with quick stats
- Browse products with advanced filters
- My Listings management
- Bookings overview
- Notifications center
- Quick add product button

## 📱 Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## 🚀 Usage

### For Equipment Owners
1. Register/Login to your account
2. Add your equipment with details and images
3. Set rental price per day
4. Manage booking requests
5. Confirm or decline bookings
6. Track your earnings

### For Renters
1. Register/Login to your account
2. Browse available equipment
3. Filter by category, location, or price
4. Book equipment for specific dates
5. Track booking status
6. Download receipts

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Authors

- Your Name - Initial work

## 🙏 Acknowledgments

- OpenStreetMap for map tiles
- Leaflet.js for map functionality
- All contributors and testers

## 📞 Support

For support, email your-email@example.com or open an issue in the repository.

## 🔄 Version History

- **v1.0.0** (2024) - Initial release
  - User authentication
  - Product management
  - Booking system
  - Notifications
  - Receipt generation
  - Cancel/Confirm features

---

Made with ❤️ for the agricultural community
