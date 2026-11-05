# AgriLease XAMPP Installation Guide

## ✅ Prerequisites Verified
- ✓ Project location: `c:\xampp\htdocs\DBMS_Agrilease\`
- ✓ Database name: `agrilease_v2`
- ✓ Configuration file: Properly configured for XAMPP

## 📋 Installation Steps

### Step 1: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Wait for both to show green "Running" status

### Step 2: Import Database

#### Option A: Using phpMyAdmin (Recommended for Beginners)
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Database name: `agrilease_v2`
4. Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**
6. Select the `agrilease_v2` database from left sidebar
7. Click **"Import"** tab at the top
8. Click **"Choose File"**
9. Select: `c:\xampp\htdocs\DBMS_Agrilease\agrilease_complete.sql`
10. Scroll down and click **"Import"**
11. Wait for success message

#### Option B: Using MySQL Command Line
1. Open Command Prompt (CMD)
2. Navigate to MySQL bin:
   ```bash
   cd c:\xampp\mysql\bin
   ```
3. Login to MySQL:
   ```bash
   mysql -u root -p
   ```
   (Press Enter when asked for password - default is empty)
4. Import the database:
   ```bash
   source c:\xampp\htdocs\DBMS_Agrilease\agrilease_complete.sql
   ```
5. Verify:
   ```bash
   SHOW DATABASES;
   USE agrilease_v2;
   SHOW TABLES;
   ```

### Step 3: Verify Database Connection
1. Open: `http://localhost/DBMS_Agrilease/index.php`
2. If you see the login page → **SUCCESS!** ✓
3. If you see a database error → Check the troubleshooting section below

### Step 4: Create Upload Directory
The images directory already exists, but verify permissions:
```bash
# Run in Command Prompt
cd c:\xampp\htdocs\DBMS_Agrilease\assets\images
# Make sure you can create files here (Windows usually allows this by default)
```

### Step 5: Test Login
Use the demo accounts:

**Account 1:**
- Username: `john_farmer`
- Password: `password` (the default bcrypt hash in DB is for "password")

**Account 2:**
- Username: `mary_agri`
- Password: `password`

## 🌐 Access URLs

- **Main Application**: http://localhost/DBMS_Agrilease/
- **Login Page**: http://localhost/DBMS_Agrilease/index.php
- **Registration**: http://localhost/DBMS_Agrilease/register.php
- **phpMyAdmin**: http://localhost/phpmyadmin

## 🔧 Configuration Details

### Database Configuration (includes/config.php)
```php
DB_HOST: localhost
DB_USER: root
DB_PASS: (empty)
DB_NAME: agrilease_v2
```

### Base URL
```php
BASE_URL: http://localhost/DBMS_Agrilease
```

## ✅ Verification Checklist

- [ ] XAMPP Apache is running (green in control panel)
- [ ] XAMPP MySQL is running (green in control panel)
- [ ] Database `agrilease_v2` exists in phpMyAdmin
- [ ] Database has 8 tables (users, products, bookings, payments, receipts, notifications, reviews)
- [ ] Can access http://localhost/DBMS_Agrilease/
- [ ] Login page displays without errors
- [ ] Can register a new account
- [ ] Can login successfully
- [ ] Dashboard displays after login

## 🐛 Troubleshooting

### Error: "Could not connect to database"
**Solution:**
1. Check MySQL is running in XAMPP Control Panel
2. Verify database name is `agrilease_v2` in phpMyAdmin
3. Check `includes/config.php` has correct credentials
4. Try restarting MySQL in XAMPP

### Error: "Access denied for user 'root'@'localhost'"
**Solution:**
1. Default XAMPP MySQL has no password for root
2. If you set a password, update `includes/config.php`:
   ```php
   define('DB_PASS', 'your_password');
   ```

### Error: 404 Not Found
**Solution:**
1. Check project is in `c:\xampp\htdocs\DBMS_Agrilease\`
2. Use URL: `http://localhost/DBMS_Agrilease/` (not `http://localhost/`)
3. Verify Apache is running

### Error: "Cannot modify header information"
**Solution:**
1. Check no output before `<?php` tags
2. Check files are saved in UTF-8 without BOM
3. Remove any spaces before `<?php`

### Images Not Uploading
**Solution:**
1. Check `assets/images/` directory exists
2. Verify folder permissions (Windows: right-click → Properties → Security)
3. Check `php.ini` settings:
   - `file_uploads = On`
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`

### Session Errors
**Solution:**
1. Check PHP session directory exists: `c:\xampp\tmp\`
2. If not, create it
3. Restart Apache

## 📊 Database Structure

The database includes:

### Tables (8)
1. **users** - User accounts and profiles
2. **products** - Equipment listings
3. **bookings** - Rental bookings
4. **payments** - Payment records
5. **receipts** - Booking receipts
6. **notifications** - User notifications
7. **reviews** - Equipment reviews

### Views (3)
- booking_details_with_receipt
- payment_summary
- product_statistics

### Triggers (3)
- before_booking_insert
- after_payment_completed
- after_booking_confirmed

### Stored Procedures (2)
- calculate_booking_duration
- get_user_booking_history

## 🔐 Default Test Accounts

The SQL file includes 2 demo users:

**User 1: John Farmer**
- Username: `john_farmer`
- Password: `password`
- Email: john@example.com
- Has 1 tractor listed

**User 2: Mary Agriculture**
- Username: `mary_agri`
- Password: `password`
- Email: mary@example.com
- Has 1 harvester listed

## 🎯 Next Steps After Installation

1. **Test Registration**: Create a new account
2. **Add Equipment**: List a new agricultural equipment
3. **Test Booking**: Book an equipment
4. **Test Payments**: Process deposit and final payments
5. **Check Notifications**: Verify notification system
6. **Update Profile**: Add profile photo and location

## 📱 Features to Test

- ✓ User registration and login
- ✓ Profile management
- ✓ Equipment listing (CRUD operations)
- ✓ Equipment browsing and search
- ✓ Booking system
- ✓ Payment processing (split 30% + 70%)
- ✓ Receipt generation
- ✓ Notification system
- ✓ GPS location features

## 🚀 Production Deployment Notes

When moving to production:

1. **Update config.php**:
   - Change database credentials
   - Set strong DB password
   - Update BASE_URL

2. **Security**:
   - Enable HTTPS
   - Set secure session settings
   - Hide error messages
   - Implement rate limiting

3. **Performance**:
   - Enable opcode caching
   - Optimize images
   - Use CDN for assets

4. **Backup**:
   - Regular database backups
   - File system backups
   - Transaction logs

## 📞 Support

If you encounter issues:
1. Check XAMPP error logs: `c:\xampp\apache\logs\error.log`
2. Check PHP errors: `c:\xampp\php\logs\php_error_log`
3. Enable error display in development (already set in config.php)

---

**Installation Date**: {DATE}  
**XAMPP Location**: c:\xampp\htdocs\DBMS_Agrilease\  
**Database Name**: agrilease_v2  
**Access URL**: http://localhost/DBMS_Agrilease/

✅ **Your setup is correct and ready to use!**
