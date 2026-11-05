import '../models/user_model.dart';
import '../models/product_model.dart';
import '../models/booking_model.dart';
import '../models/payment_model.dart';
import '../models/receipt_model.dart';
import '../models/notification_model.dart';

class MockDatabase {
  static final MockDatabase _instance = MockDatabase._internal();
  factory MockDatabase() => _instance;

  MockDatabase._internal() {
    _initData();
  }

  UserModel? currentUser;
  final List<UserModel> users = [];
  final List<ProductModel> products = [];
  final List<BookingModel> bookings = [];
  final List<PaymentModel> payments = [];
  final List<ReceiptModel> receipts = [];
  final List<NotificationModel> notifications = [];

  void _initData() {
    // 1. Initial Users (Matching SQL seed data)
    users.addAll([
      const UserModel(
        id: 1,
        username: 'john_farmer',
        fullName: 'John Farmer',
        email: 'john@example.com',
        phone: '+91 98765 43210',
        address: 'Village Road, Ludhiana, Punjab',
        lat: 30.7333,
        lng: 76.7794,
        userType: 'both',
      ),
      const UserModel(
        id: 2,
        username: 'mary_agri',
        fullName: 'Mary Agriculture',
        email: 'mary@example.com',
        phone: '+91 98765 43211',
        address: 'Farm Street, Karnal, Haryana',
        lat: 29.0588,
        lng: 76.0856,
        userType: 'both',
      ),
    ]);

    currentUser = users[0]; // Default logged-in as John Farmer

    // 2. Initial Products (Matching SQL seed data & categories)
    products.addAll([
      const ProductModel(
        id: 1,
        title: 'John Deere 5050D Tractor',
        description: 'Powerful 50HP tractor suitable for all farming operations including ploughing, tilling, and heavy trailer transport. Equipped with power steering and oil immersed disc brakes.',
        category: 'Tractors',
        price: 1500.0,
        imagePath: 'assets/images/tractor.png',
        location: 'Ludhiana, Punjab',
        lat: 30.7333,
        lng: 76.7794,
        listedBy: 1,
        status: 'available',
        views: 142,
        averageRating: 4.8,
        reviewCount: 12,
        ownerName: 'John Farmer',
        ownerPhone: '+91 98765 43210',
      ),
      const ProductModel(
        id: 2,
        title: 'New Holland TC5.90 Combine Harvester',
        description: 'High-capacity combine harvester optimized for wheat, paddy, and soybean. Low grain loss system and spacious air-conditioned operator cab.',
        category: 'Harvesters',
        price: 3500.0,
        imagePath: 'assets/images/harvester.png',
        location: 'Karnal, Haryana',
        lat: 29.0588,
        lng: 76.0856,
        listedBy: 2,
        status: 'available',
        views: 289,
        averageRating: 4.9,
        reviewCount: 24,
        ownerName: 'Mary Agriculture',
        ownerPhone: '+91 98765 43211',
      ),
      const ProductModel(
        id: 3,
        title: 'Mahindra Rotary Tiller 7ft',
        description: 'Heavy duty rotary tiller / rotavator suitable for wet and dry soil preparation. 48 boron steel blades for fine seedbed preparation.',
        category: 'Tillers',
        price: 800.0,
        imagePath: 'assets/images/tiller.png',
        location: 'Bathinda, Punjab',
        lat: 30.2110,
        lng: 74.9455,
        listedBy: 2,
        status: 'available',
        views: 95,
        averageRating: 4.6,
        reviewCount: 7,
        ownerName: 'Mary Agriculture',
        ownerPhone: '+91 98765 43211',
      ),
      const ProductModel(
        id: 4,
        title: 'Pneumatic Precision Multi-Crop Seeder',
        description: 'Pneumatic planter for zero-tillage seed and fertilizer placement with adjustable seed spacing and uniform depth.',
        category: 'Seeders',
        price: 1200.0,
        imagePath: 'assets/images/seeder.png',
        location: 'Ambala, Haryana',
        lat: 30.3782,
        lng: 76.7767,
        listedBy: 1,
        status: 'available',
        views: 63,
        averageRating: 4.7,
        reviewCount: 4,
        ownerName: 'John Farmer',
        ownerPhone: '+91 98765 43210',
      ),
      const ProductModel(
        id: 5,
        title: 'Tractor Mounted Boom Sprayer 600L',
        description: '600 Litre poly tank boom sprayer with 12-meter hydraulic folding boom, anti-drip nozzle bodies, and pressure regulator for pesticide/fungicide spraying.',
        category: 'Sprayers',
        price: 950.0,
        imagePath: 'assets/images/sprayer.png',
        location: 'Patiala, Punjab',
        lat: 30.3398,
        lng: 76.3869,
        listedBy: 2,
        status: 'available',
        views: 110,
        averageRating: 4.5,
        reviewCount: 8,
        ownerName: 'Mary Agriculture',
        ownerPhone: '+91 98765 43211',
      ),
    ]);

    // 3. Initial Bookings (Demonstrating 30% deposit / 70% final split payment)
    bookings.addAll([
      BookingModel(
        id: 1,
        productId: 2, // New Holland Harvester
        renterId: 1,  // John rented from Mary
        ownerId: 2,
        status: 'confirmed',
        startDate: DateTime.now().add(const Duration(days: 3)),
        endDate: DateTime.now().add(const Duration(days: 5)),
        totalPrice: 10500.0,
        depositAmount: 3150.0, // 30%
        depositPaid: true,
        depositPaidDate: DateTime.now().subtract(const Duration(days: 1)),
        finalAmount: 7350.0,   // 70%
        finalPaid: false,
        paymentStatus: 'pending',
        paymentMethod: 'UPI',
        transactionId: 'TXN-98421034',
        createdAt: DateTime.now().subtract(const Duration(days: 2)),
        productTitle: 'New Holland TC5.90 Combine Harvester',
        productImage: 'assets/images/harvester.png',
        dailyPrice: 3500.0,
        renterName: 'John Farmer',
        ownerName: 'Mary Agriculture',
      ),
      BookingModel(
        id: 2,
        productId: 1, // John Deere Tractor
        renterId: 2,  // Mary rented from John
        ownerId: 1,
        status: 'pending',
        startDate: DateTime.now().add(const Duration(days: 7)),
        endDate: DateTime.now().add(const Duration(days: 8)),
        totalPrice: 3000.0,
        depositAmount: 900.0,
        depositPaid: false,
        finalAmount: 2100.0,
        finalPaid: false,
        paymentStatus: 'pending',
        notes: 'Need for field cultivation before paddy sowing.',
        createdAt: DateTime.now().subtract(const Duration(hours: 4)),
        productTitle: 'John Deere 5050D Tractor',
        productImage: 'assets/images/tractor.png',
        dailyPrice: 1500.0,
        renterName: 'Mary Agriculture',
        ownerName: 'John Farmer',
      ),
    ]);

    // 4. Initial Receipts
    receipts.add(
      ReceiptModel.fromBooking(
        id: 1,
        bookingId: 1,
        totalPrice: 10500.0,
        depositAmount: 3150.0,
        finalAmount: 7350.0,
        depositPaid: true,
        finalPaid: false,
        paymentMethod: 'UPI',
      ),
    );

    // 5. Initial Notifications
    notifications.addAll([
      NotificationModel(
        id: 1,
        userId: 1,
        title: 'Deposit Received',
        message: 'Your 30% deposit of ₹3,150 for New Holland Harvester has been successfully processed.',
        type: 'payment',
        isRead: false,
        relatedId: 1,
        relatedType: 'booking',
        createdAt: DateTime.now().subtract(const Duration(hours: 3)),
      ),
      NotificationModel(
        id: 2,
        userId: 1,
        title: 'New Booking Request',
        message: 'Mary Agriculture requested to book John Deere 5050D Tractor for 2 days.',
        type: 'booking',
        isRead: false,
        relatedId: 2,
        relatedType: 'booking',
        createdAt: DateTime.now().subtract(const Duration(hours: 4)),
      ),
    ]);
  }
}
