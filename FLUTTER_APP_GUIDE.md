# AgriLease - Flutter Mobile & Web Application

Welcome to the converted **AgriLease** Flutter application. This project transforms the original PHP/MySQL DBMS platform into a modern, production-ready, cross-platform Flutter application built with Flutter & Dart 3.

---

## 🏗️ Architectural Overview

The application follows the official layered Flutter architecture:

```
lib/
├── core/
│   ├── constants/app_constants.dart    # Categories, payment options, status codes
│   ├── theme/app_theme.dart            # Material 3 Agricultural Green theme
│   └── utils/formatters.dart           # Currency (₹ INR), date and time formatting
├── data/
│   ├── models/                         # Domain & Data Models
│   │   ├── user_model.dart             # Farmer profile with lat/lng & roles
│   │   ├── product_model.dart          # Machinery listing & specs
│   │   ├── booking_model.dart          # Booking state & duration calculation
│   │   ├── payment_model.dart          # Deposit & balance payment transactions
│   │   ├── receipt_model.dart          # Formal Tax Invoice (RCP-XXXXXXXX)
│   │   └── notification_model.dart     # System & booking alerts
│   ├── services/
│   │   ├── api_service.dart            # Client for PHP REST backend
│   │   └── mock_database.dart          # In-memory reactive state with SQL seed data
│   └── repositories/
│       ├── auth_repository.dart        # Authentication & profile management
│       ├── product_repository.dart     # Catalog queries, search & filtering
│       ├── booking_repository.dart     # Booking lifecycle & split payments
│       └── notification_repository.dart# Read/unread notifications
├── providers/                          # State Management (Provider / ChangeNotifier)
│   ├── auth_provider.dart
│   ├── products_provider.dart
│   ├── bookings_provider.dart
│   └── notifications_provider.dart
├── ui/
│   ├── common/                         # Reusable Widgets
│   │   ├── custom_button.dart
│   │   ├── custom_text_field.dart
│   │   ├── status_badge.dart
│   │   └── product_card.dart
│   └── features/                       # Feature Screens
│       ├── auth/
│       │   ├── login_screen.dart       # One-tap demo switcher & login
│       │   └── register_screen.dart    # New farmer registration
│       ├── dashboard/
│       │   └── dashboard_screen.dart   # Metrics, quick actions, listings
│       ├── products/
│       │   ├── products_screen.dart    # Search, category chips, grid
│       │   ├── product_detail_screen.dart # Specifications, pricing, GPS
│       │   ├── add_edit_product_screen.dart # Form to list machinery
│       │   └── my_products_screen.dart # Owner's listed equipment
│       ├── bookings/
│       │   ├── book_product_screen.dart # Date picker & 30/70 split computation
│       │   ├── booking_confirmed_screen.dart # Booking success voucher
│       │   ├── my_bookings_screen.dart # Renter booking history & pay CTA
│       │   └── manage_bookings_screen.dart # Owner request approval
│       ├── payments/
│       │   ├── payment_screen.dart     # UPI, Net Banking, Card, COD
│       │   └── receipt_screen.dart     # Official Tax Invoice view & download
│       ├── notifications/
│       │   └── notifications_screen.dart # Alert center
│       ├── profile/
│       │   └── profile_screen.dart     # User profile & switcher
│       └── navigation/
│           └── main_navigation_screen.dart # Bottom tab navigation
└── main.dart                           # App entry point & provider tree
```

---

## 🔄 Mapping: Original PHP/SQL to Flutter/Dart

| Original PHP / SQL File | Converted Flutter Component | Purpose |
|-------------------------|-----------------------------|---------|
| `index.php` / `auth.php` | `login_screen.dart`, `auth_provider.dart` | Secure login, session state, demo farmer switcher |
| `register.php` | `register_screen.dart` | Farmer sign-up with role selection (`renter`, `owner`, `both`) |
| `dashboard.php` | `dashboard_screen.dart` | Metric cards (active hires, listings, requests), quick actions |
| `products.php` | `products_screen.dart` | Browse machinery with live category chips & search query |
| `product_detail.php` | `product_detail_screen.dart` | Detailed specifications, rate per day, owner contact & location |
| `add_product.php` | `add_edit_product_screen.dart` | Add new agricultural machinery to marketplace |
| `my_products.php` | `my_products_screen.dart` | Owner's personal fleet of equipment |
| `book.php` | `book_product_screen.dart` | Date range selector, automated duration & price calculation |
| `booking_confirmed.php` | `booking_confirmed_screen.dart` | Booking voucher & deposit checkout trigger |
| `my_bookings.php` | `my_bookings_screen.dart` | Renter dashboard with deposit & final balance status |
| `manage_bookings.php` | `manage_bookings_screen.dart` | Owner view to confirm, reject, or complete hire requests |
| `payment.php` / `payment_gateway.php` | `payment_screen.dart` | Multi-method payment checkout (UPI, Card, Bank, COD) |
| `receipt.php` | `receipt_screen.dart` | Formal tax invoice with GST (18%) and `RCP-XXXXXXXX` ID |
| `notifications.php` | `notifications_screen.dart` | Real-time notifications and unread badge counters |
| `profile.php` | `profile_screen.dart` | Farmer account management and demo role switcher |
| `agrilease_complete.sql` Triggers | `BookingRepository` methods | Replicated `before_booking_insert` (30/70 split), `after_payment_completed`, and `after_booking_confirmed` triggers in pure Dart |

---

## ⚡ How to Run

1. **Get Dependencies:**
   ```bash
   flutter pub get
   ```

2. **Run on Mobile or Chrome/Web:**
   ```bash
   # Run in Chrome
   flutter run -d chrome

   # Run on connected Android / iOS device
   flutter run
   ```

3. **Run Unit Tests:**
   ```bash
   flutter test
   ```

4. **Original PHP Backend:**
   The original PHP codebase is preserved in [`backend_php/`](./backend_php/) for backend reference or serving REST APIs.
