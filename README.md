# AgriLease - Agricultural Equipment Rental Platform

## 📋 Project Overview

AgriLease is a comprehensive web-based platform designed to facilitate the rental of agricultural equipment between farmers and equipment owners. The platform enables farmers to list their equipment for rent and allows other farmers to book equipment they need, creating a sharing economy for agricultural machinery.

## 🎯 Key Features

### User Management
- **User Registration & Authentication**: Secure login/logout system with password hashing
- **Profile Management**: Users can update personal information, contact details, and location
- **Role-based Access**: Differentiation between equipment owners and renters

### Equipment Management
- **Product Listings**: Add, edit, and delete agricultural equipment listings
- **Category System**: Organized equipment categories (Tractors, Harvesters, Tillers, Seeders, Sprayers, Irrigation, Trailers)
- **Image Upload**: Support for equipment photos
- **GPS Location**: Auto-detect and manual location entry for equipment
- **Availability Status**: Real-time tracking of equipment availability

### Booking System
- **Date-based Booking**: Select start and end dates for equipment rental
- **Booking Validation**: Prevents double-booking and date conflicts
- **Booking Status**: Pending, Confirmed, Completed, Cancelled states
- **Booking History**: Track all past and current bookings

### Payment System
- **Split Payment Model**: 30% deposit + 70% final payment structure
- **Multiple Payment Methods**: Cash, Bank Transfer, UPI, Card, Cheque
- **Payment Tracking**: Complete payment history and status
- **Payment Verification**: Owner verification of received payments
- **Transaction Records**: Transaction IDs and reference numbers

### Notification System
- **Real-time Notifications**: Alerts for bookings, payments, and status changes
- **Notification Types**: Booking, Payment, System notifications
- **Read/Unread Status**: Track notification status
- **Notification Center**: Centralized notification management

### Search & Filter
- **Advanced Search**: Search by equipment name, description
- **Category Filter**: Filter by equipment category
- **Location Filter**: Find equipment by location
- **Price Range Filter**: Filter by minimum and maximum price

### Dashboard
- **Statistics Overview**: Total listings, bookings, earnings
- **My Products**: Manage personal equipment listings
- **Browse Products**: Explore available equipment
- **Received Bookings**: View and manage booking requests
- **Quick Actions**: Fast access to common tasks

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with custom properties, gradients, animations
- **JavaScript**: Interactive features, form validation, geolocation
- **SVG Icons**: Scalable vector graphics for UI elements

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL 5.7+**: Relational database management
- **PDO**: Database abstraction layer for secure queries

### Security
- **CSRF Protection**: Token-based form security
- **Password Hashing**: bcrypt algorithm
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling

## 📁 Project Structure

```
agrilease/
├── assets/
│   ├── css/              # Stylesheets
│   └── images/           # Image assets and uploads
├── includes/
│   ├── auth.php          # Authentication middleware
│   ├── config.php        # Database configuration
│   ├── functions.php     # Helper functions
│   ├── header.php        # Common header
│   ├── footer.php        # Common footer
│   └── init.php          # Initialization script
├── index.php             # Login page
├── register.php          # User registration
├── dashboard.php         # Main dashboard
├── products.php          # Browse equipment
├── product_detail.php    # Equipment details
├── add_product.php       # Add new equipment
├── edit_product.php      # Edit equipment
├── my_products.php       # User's equipment listings
├── book.php              # Booking processor
├── booking_confirmed.php # Booking confirmation
├── my_bookings.php       # User's bookings
├── manage_bookings.php   # Manage received bookings
├── payment.php           # Payment management
├── payment_gateway.php   # Payment processing
├── receipt.php           # Booking receipt
├── notifications.php     # Notification center
├── profile.php           # User profile
├── logout.php            # Logout handler
└── agrilease_complete.sql # Database schema
```

## 🗄️ Database Schema

### Tables

#### users
- User account information
- Personal details (name, email, phone)
- Location data (address, GPS coordinates)
- Authentication credentials

#### products
- Equipment listings
- Product details (title, description, category)
- Pricing information
- Location and GPS coordinates
- Availability status
- Image path

#### bookings
- Rental bookings
- Date range (start_date, end_date)
- Booking status
- Price calculations
- Renter and owner information
- GPS coordinates for both parties

#### payments
- Payment records
- Payment type (deposit/final)
- Amount and status
- Payment method
- Transaction details
- Verification information

#### receipts
- Booking receipts
- Payment status
- Receipt generation date

#### notifications
- User notifications
- Notification type and content
- Read/unread status
- Timestamps

#### reviews
- Equipment reviews and ratings
- Review content
- Rating (1-5 stars)

### Database Views
- `booking_details_with_receipt`: Complete booking information with receipt data
- `payment_summary`: Aggregated payment information
- `product_statistics`: Equipment usage statistics

### Stored Procedures
- `calculate_booking_duration`: Calculate rental duration
- `get_user_booking_history`: Retrieve user's booking history

### Triggers
- `before_booking_insert`: Validate booking before insertion
- `after_payment_completed`: Update booking status after payment
- `after_booking_confirmed`: Send notifications on booking confirmation

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd agrilease
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the SQL file:
   ```bash
   mysql -u username -p database_name < agrilease_complete.sql
   ```

3. **Configure Database Connection**
   - Edit `includes/config.php`
   - Update database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'agrilease_v2';
   $username = 'your_username';
   $password = 'your_password';
   ```

4. **Set File Permissions**
   ```bash
   chmod 755 assets/images/
   chmod 644 includes/config.php
   ```

5. **Configure Web Server**
   - Point document root to project directory
   - Enable mod_rewrite (Apache)
   - Restart web server

6. **Access the Application**
   - Open browser and navigate to: `http://localhost/agrilease`
   - Register a new account or use demo credentials

## 👤 Default Users

After importing the database, you can use these demo accounts:

- **User 1**: 
  - Username: `farmer1`
  - Password: `password123`
  
- **User 2**: 
  - Username: `farmer2`
  - Password: `password123`

## 🔧 Configuration

### File Upload Settings
- Maximum file size: 5MB
- Allowed formats: JPG, JPEG, PNG
- Upload directory: `assets/images/`

### Session Settings
- Session timeout: 24 hours
- Secure cookies enabled
- CSRF token validation

### Payment Settings
- Deposit percentage: 30%
- Final payment percentage: 70%
- Supported payment methods: Cash, Bank Transfer, UPI, Card, Cheque

## 📱 Features in Detail

### Equipment Listing
1. Navigate to "Add Product"
2. Fill in equipment details
3. Upload equipment photo
4. Set daily rental price
5. Add location (manual or GPS auto-detect)
6. Submit listing

### Booking Process
1. Browse available equipment
2. Select equipment
3. Choose rental dates
4. Review booking details
5. Submit booking request
6. Make deposit payment (30%)
7. Complete final payment (70%)

### Payment Flow
1. Renter makes payment
2. Payment marked as "Pending"
3. Owner verifies payment
4. Payment status updated to "Completed"
5. Booking status updated accordingly

## 🎨 UI/UX Features

- **Responsive Design**: Mobile-friendly interface
- **Modern Aesthetics**: Gradient backgrounds, smooth animations
- **Intuitive Navigation**: Clear menu structure
- **Visual Feedback**: Loading states, success/error messages
- **Card-based Layout**: Clean, organized content presentation
- **Icon System**: SVG icons for better scalability

## 🔒 Security Features

- CSRF token protection on all forms
- Password hashing with bcrypt
- SQL injection prevention via prepared statements
- XSS protection through input sanitization
- Session hijacking prevention
- Secure file upload validation

## 📊 Reporting & Analytics

- Total equipment listings
- Active bookings count
- Revenue tracking
- Booking history
- Payment history
- Equipment utilization statistics

## 🌐 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Opera (latest)

## 📝 API Endpoints (Internal)

All endpoints are PHP-based and handle both GET and POST requests:

- `/index.php` - Login
- `/register.php` - Registration
- `/dashboard.php` - Main dashboard
- `/products.php` - Equipment listing
- `/product_detail.php?id={id}` - Equipment details
- `/book.php` - Booking submission
- `/payment.php?id={booking_id}` - Payment management
- `/receipt.php?id={booking_id}` - Booking receipt
- `/notifications.php` - Notification center

## 🐛 Known Issues & Limitations

- File upload limited to 5MB
- GPS location requires browser permission
- Payment gateway integration is simulated (not connected to real payment processors)
- Email notifications not implemented (uses in-app notifications only)

## 🔄 Future Enhancements

- [ ] Real payment gateway integration (Razorpay, PayPal)
- [ ] Email notification system
- [ ] SMS notifications
- [ ] Advanced analytics dashboard
- [ ] Equipment maintenance tracking
- [ ] Insurance integration
- [ ] Mobile app (iOS/Android)
- [ ] Multi-language support
- [ ] Equipment damage reporting
- [ ] Rating and review system enhancement
- [ ] Chat system between users
- [ ] Calendar view for bookings
- [ ] Equipment comparison feature
- [ ] Wishlist functionality

## 📄 License

This project is developed for educational and commercial purposes. All rights reserved.

## 👥 Contributors

- Development Team: AgriLease Development Team
- Project Type: Agricultural Equipment Rental Platform
- Version: 2.0

## 📞 Support

For support, issues, or feature requests:
- Create an issue in the repository
- Contact: support@agrilease.com
- Documentation: Available in project wiki

## 🙏 Acknowledgments

- Icons: Custom SVG icons
- Design inspiration: Modern web design principles
- Database design: Normalized relational database structure

---

**Last Updated**: November 2025  
**Version**: 2.0  
**Status**: Production Ready
