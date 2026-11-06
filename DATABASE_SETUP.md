# Database Setup for AgriLease Flutter App

## Quick Start (15 minutes)

### Step 1: Install & Start XAMPP ⚙️

1. **Download XAMPP** from https://www.apachefriends.org/
2. **Install** to `C:\xampp` (default location)
3. **Open** XAMPP Control Panel
4. **Start** Apache (web server)
5. **Start** MySQL (database)

✅ Both should show green "Running" indicators

### Step 2: Import Database 🗄️

1. **Open** http://localhost/phpmyadmin (in your browser)
2. **Click** "New" in the left sidebar
3. **Enter** database name: `agrilease_v2`
4. **Click** "Create"
5. **Select** the new `agrilease_v2` database
6. **Click** "Import" tab
7. **Choose File:** `agrilease_complete.sql` from your project
8. **Click** "Import" and wait for success message

✅ Database is now ready with all tables, users, and sample data

### Step 3: Verify Database Connection 🔍

Open Command Prompt and run:
```bash
mysql -u root -p
# Press Enter (password is empty by default)

USE agrilease_v2;
SELECT COUNT(*) FROM products;
# Should return: 5
```

✅ Database is working!

### Step 4: Configure Flutter App 📱

**Option A: Web/Localhost (Easiest)**
- Flutter app and XAMPP on same computer
- No changes needed - already configured for `http://localhost`

**Option B: Local Network (Mobile Device)**
- Find your computer's IP address:
  ```bash
  ipconfig
  # Look for "IPv4 Address" (e.g., 192.168.1.100)
  ```
- Edit `lib/core/config/app_config.dart`:
  ```dart
  static String get apiBaseUrl {
    return 'http://192.168.1.100/DBMS_Agrilease/api'; // Change IP
  }
  ```

### Step 5: Enable API Mode in Flutter ✨

Edit `lib/core/config/app_config.dart`:
```dart
// Change this:
static const bool enableMockData = true;

// To this:
static const bool enableMockData = false;
```

### Step 6: Run the App 🚀

```bash
cd c:\Users\hp\.gemini\antigravity\scratch\DBMS_Agrilease

# Get new dependencies
flutter pub get

# Run on Chrome (web)
flutter run -d chrome

# Or run on Android device
flutter run
```

✅ App now uses real database instead of mock data!

---

## Verification Checklist

- [ ] XAMPP is running (Apache + MySQL green)
- [ ] Database `agrilease_v2` exists in phpMyAdmin
- [ ] `products` table has 5 rows
- [ ] `users` table has 2 rows (john_farmer, mary_agri)
- [ ] `enableMockData = false` in app_config.dart
- [ ] `flutter pub get` completed successfully
- [ ] App starts without errors
- [ ] Products load from database (not mock)
- [ ] Can login with john_farmer / mary_agri
- [ ] Product details show real data

---

## API Endpoints Available

The app now calls these REST API endpoints:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/products/fetch.php` | GET | Fetch all products (with filters) |
| `api/products/detail.php?id=1` | GET | Get single product details |
| `api/auth/login.php` | POST | Login user |
| `api/bookings/user.php?userId=1` | GET | Get user's bookings |
| More coming... | | |

All endpoints:
- Return JSON responses
- Include proper error handling
- Support CORS headers
- Work with real MySQL data

---

## Configuration Reference

### API URL Settings

`lib/core/config/app_config.dart` has these options:

```dart
// For local web development (default)
static const String localApiUrl = 'http://localhost/DBMS_Agrilease/api';

// For local network (mobile device)
static const String localMobileApiUrl = 'http://192.168.1.100/DBMS_Agrilease/api';

// For production (future)
static const String productionApiUrl = 'https://api.agrilease.com';

// Change this to switch:
static String get apiBaseUrl {
  return localApiUrl; // ← Change this
}
```

### Feature Flags

```dart
// Use real API instead of mock data
static const bool enableMockData = false;

// Show debug logs in console
static const bool enableLogging = true;

// Enable analytics (future)
static const bool enableAnalytics = false;
```

### Database Configuration

If you changed XAMPP settings, update these:

```dart
static const String dbName = 'agrilease_v2';    // Database name
static const String dbHost = 'localhost';        // MySQL host
static const String dbUser = 'root';             // MySQL username
static const String dbPassword = '';             // MySQL password
```

---

## Troubleshooting

### Error: "Connection refused"
```
❌ XAMPP not running
✅ Start Apache and MySQL in XAMPP Control Panel
```

### Error: "404 Not Found" for API
```
❌ API files not in correct location
✅ Files should be in:
   - backend_php/api/products/fetch.php
   - backend_php/api/auth/login.php
   - etc.
```

### Error: "Table 'agrilease_v2.products' doesn't exist"
```
❌ Database not imported
✅ Import agrilease_complete.sql file in phpMyAdmin
```

### App still shows mock data
```
❌ enableMockData = true
✅ Change to enableMockData = false in app_config.dart
```

### Can login but products don't load
```
❌ API endpoint path wrong
✅ Check:
   - XAMPP is running
   - API files exist in backend_php/api/
   - URL in app_config.dart matches your setup
   - Browser can access http://localhost/DBMS_Agrilease/api/products/fetch.php
```

### Mobile device can't connect
```
❌ Using localhost URL
✅ Change to your computer's IP:
   - Find IP: ipconfig (look for IPv4 Address)
   - Update apiBaseUrl to http://192.168.x.x/DBMS_Agrilease/api
   - Ensure both device and computer on same WiFi
```

---

## Testing API Manually

Before testing in app, verify API works:

1. **Test Products Fetch:**
   ```
   http://localhost/DBMS_Agrilease/api/products/fetch.php
   ```
   Should return JSON with 5 products

2. **Test With Filter:**
   ```
   http://localhost/DBMS_Agrilease/api/products/fetch.php?category=Tractors
   ```
   Should return only tractors

3. **Test Search:**
   ```
   http://localhost/DBMS_Agrilease/api/products/fetch.php?search=John
   ```
   Should return products matching search

4. **Test Product Detail:**
   ```
   http://localhost/DBMS_Agrilease/api/products/detail.php?id=1
   ```
   Should return single product JSON

All should return valid JSON. If error, check:
- PHP error logs: `C:\xampp\php\logs\php_error_log`
- Apache error logs: `C:\xampp\apache\logs\error.log`

---

## Next Steps

### Completed ✅
- [x] Database schema created
- [x] Sample data seeded
- [x] REST API endpoints implemented
- [x] Flutter app API integration

### Ready to Implement 🔄
- [ ] User registration API
- [ ] Create booking API
- [ ] Process payment API
- [ ] Image upload API
- [ ] Push notifications

### Future Enhancements 🚀
- [ ] Real payment gateway (Razorpay, PayPal)
- [ ] Email notifications
- [ ] SMS alerts
- [ ] Admin dashboard
- [ ] Analytics
- [ ] Production deployment

---

## Database Schema Overview

The `agrilease_v2` database contains:

### Tables (7)
1. **users** - User accounts and profiles
2. **products** - Equipment listings
3. **bookings** - Rental bookings
4. **payments** - Payment records
5. **receipts** - Booking receipts
6. **notifications** - User notifications
7. **reviews** - Equipment reviews

### Users (Sample)
- **john_farmer** (id: 1)
- **mary_agri** (id: 2)

### Products (Sample)
1. John Deere 5050D Tractor - ₹1,500/day
2. New Holland Harvester - ₹3,500/day
3. Mahindra Rotary Tiller - ₹800/day
4. Pneumatic Seeder - ₹1,200/day
5. Boom Sprayer - ₹950/day

---

## Performance Tips

1. **Database Indexing**
   - Products indexed on category and status
   - Bookings indexed on user IDs
   - This speeds up queries significantly

2. **Connection Pooling**
   - XAMPP uses connection pooling by default
   - Multiple requests share connections

3. **Caching**
   - Consider caching product list (changes rarely)
   - Cache by category separately
   - Invalidate cache when products updated

4. **Query Optimization**
   - Current queries are already optimized
   - Limit results (100 products max)
   - Use WHERE clauses for filtering

---

## Security Notes

### Current Development Setup
- ✅ Suitable for development
- ⚠️ NOT suitable for production
- Default XAMPP has no authentication

### For Production
- [ ] Use HTTPS/SSL certificates
- [ ] Implement proper authentication
- [ ] Add rate limiting
- [ ] Validate all inputs
- [ ] Use parameterized queries
- [ ] Implement CORS properly
- [ ] Use environment variables for secrets
- [ ] Enable XAMPP security features

---

## Support & Resources

- **XAMPP Help:** https://www.apachefriends.org/
- **phpMyAdmin:** http://localhost/phpmyadmin
- **PHP Docs:** https://www.php.net/
- **MySQL Reference:** https://dev.mysql.com/doc/

---

## Version Information

- **XAMPP:** 8.0+ (PHP 8.0, MySQL 5.7+)
- **Flutter:** 3.x
- **Dart:** 3.x
- **Database:** MySQL 5.7 or MariaDB
- **PHP:** 8.0+

---

**Setup Date:** November 2026
**Status:** ✅ Ready for Development
**Next Steps:** Start app and begin testing!
