# AgriLease - Agricultural Equipment Rental Platform

A comprehensive web application for renting and managing agricultural equipment. Built with PHP, MySQL, and modern web technologies.

## Features

### Core Functionality
- **User Registration & Authentication** - Secure user accounts with password hashing
- **Equipment Listing** - Add, edit, and manage agricultural equipment for rent
- **Advanced Search & Filtering** - Find equipment by category, location, and price range
- **Booking System** - Request and manage equipment rentals with date ranges
- **Booking Management** - Approve, decline, and track booking requests
- **Real-time Notifications** - Stay updated on booking status and system events
- **Interactive Maps** - Location-based equipment discovery using Leaflet maps
- **Receipt Generation** - Detailed booking receipts with pricing breakdown
- **Profile Management** - Update personal information and account settings
- **Responsive Design** - Works seamlessly on desktop and mobile devices

### User Roles
- **Equipment Owners** - List equipment, manage bookings, track earnings
- **Renters** - Browse equipment, make bookings, track rental history
- **Dual Role Support** - Users can both list and rent equipment

## Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Maps**: Leaflet.js with OpenStreetMap
- **Security**: CSRF protection, password hashing, input sanitization
- **File Uploads**: Secure image handling with validation

## Installation & Setup

### Prerequisites
- Web server (Apache/Nginx) with PHP 7.4+
- MySQL 5.7+ or MariaDB
- GD extension for PHP (for image handling)

### Step 1: Database Setup
1. Create a MySQL database named `agrilease_v2`
2. Import the database schema:
   ```bash
   mysql -u your_username -p agrilease_v2 < agrilease_final.sql
   ```

### Step 2: Configuration
1. Update database credentials in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'agrilease_v2');
   ```

2. Update the base URL if needed:
   ```php
   define('BASE_URL', 'http://your-domain.com/agrilease');
   ```

### Step 3: File Permissions
Ensure the `assets/images/` directory is writable:
```bash
chmod 755 assets/images/
```

### Step 4: Access the Application
1. Place the project files in your web server root
2. Visit `http://your-domain.com/agrilease/index.php`
3. Register a new account or use sample credentials

## Sample Data
The database includes sample users and equipment:

**Sample Users** (password: `password` for all):
- `farmer1` - Rajesh Kumar (Haryana)
- `owner1` - Suresh Patel (Gujarat) 
- `renter1` - Amit Singh (Punjab)

**Sample Equipment**:
- John Deere 5050D Tractor - ₹1,500/day
- Mahindra Harvester - ₹2,500/day
- Rotary Tiller - ₹800/day
- Seed Drill Machine - ₹600/day

## File Structure
```
agrilease/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── includes/
│   ├── auth.php
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── index.php (Login)
├── register.php
├── dashboard.php
├── products.php (Browse Equipment)
├── product_detail.php
├── add_product.php
├── edit_product.php
├── my_products.php
├── book.php
├── manage_bookings.php
├── my_bookings.php
├── receipt.php
├── notifications.php
├── profile.php
├── logout.php
└── agrilease_final.sql
```

## Key Pages & Functionality

### Authentication
- **index.php** - User login with session management
- **register.php** - New user registration with validation
- **logout.php** - Secure session termination

### Equipment Management
- **products.php** - Browse all available equipment with filters
- **product_detail.php** - Detailed equipment view with booking form
- **add_product.php** - Add new equipment listings
- **edit_product.php** - Modify existing listings
- **my_products.php** - Manage your equipment listings

### Booking System
- **book.php** - Process booking requests
- **manage_bookings.php** - Handle incoming booking requests
- **my_bookings.php** - View your booking history
- **receipt.php** - Detailed booking receipts with maps

### User Management
- **dashboard.php** - Main dashboard with overview
- **profile.php** - Update personal information
- **notifications.php** - View system notifications

## Security Features
- CSRF token protection on all forms
- Password hashing using PHP's password_hash()
- Input sanitization and validation
- File upload security with type/size validation
- SQL injection prevention using prepared statements
- Session security with proper cookie handling

## Database Schema

### Users Table
- User authentication and profile information
- Location coordinates for mapping
- Profile image support

### Products Table
- Equipment listings with categories
- Pricing and availability status
- Location and image storage
- Owner relationship

### Bookings Table
- Rental requests and confirmations
- Date ranges and pricing
- Status tracking (pending/confirmed/completed/cancelled)
- Location coordinates for both parties

### Notifications Table
- System notifications by type
- Read/unread status tracking
- Categorized messaging

### Reviews Table (Future Enhancement)
- User rating system
- Booking-based reviews

## Customization
- Modify categories in the database or forms
- Update styling in `assets/css/style.css`
- Add new notification types in `includes/functions.php`
- Extend user profiles with additional fields

## Troubleshooting

### Common Issues
1. **Database Connection Error**: Check credentials in `config.php`
2. **Image Upload Fails**: Verify `assets/images/` permissions
3. **Maps Not Loading**: Check internet connection for Leaflet CDN
4. **Session Issues**: Ensure PHP sessions are enabled

### Error Logging
Enable PHP error logging for debugging:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Future Enhancements
- Payment gateway integration
- SMS notifications
- Advanced search with radius filtering
- Equipment availability calendar
- Rating and review system
- Mobile app development
- Multi-language support

## Support
For technical support or feature requests, please contact the development team.

## License
This project is developed for educational and commercial use.
