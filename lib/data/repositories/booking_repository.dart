import '../models/booking_model.dart';
import '../models/receipt_model.dart';
import '../models/payment_model.dart';
import '../models/notification_model.dart';
import '../services/mock_database.dart';

class BookingRepository {
  final MockDatabase _db = MockDatabase();

  Future<List<BookingModel>> getUserBookings(int renterId) async {
    await Future.delayed(const Duration(milliseconds: 150));
    return _db.bookings.where((b) => b.renterId == renterId).toList();
  }

  Future<List<BookingModel>> getOwnerBookings(int ownerId) async {
    await Future.delayed(const Duration(milliseconds: 150));
    return _db.bookings.where((b) => b.ownerId == ownerId).toList();
  }

  Future<BookingModel?> getBookingById(int bookingId) async {
    await Future.delayed(const Duration(milliseconds: 100));
    try {
      return _db.bookings.firstWhere((b) => b.id == bookingId);
    } catch (_) {
      return null;
    }
  }

  Future<ReceiptModel?> getReceiptForBooking(int bookingId) async {
    await Future.delayed(const Duration(milliseconds: 100));
    try {
      return _db.receipts.firstWhere((r) => r.bookingId == bookingId);
    } catch (_) {
      return null;
    }
  }

  /// Implements SQL trigger: before_booking_insert (30% deposit, 70% final)
  Future<BookingModel> createBooking({
    required int productId,
    required int renterId,
    required DateTime startDate,
    required DateTime endDate,
    String? notes,
  }) async {
    await Future.delayed(const Duration(milliseconds: 300));
    final product = _db.products.firstWhere((p) => p.id == productId);
    final renter = _db.users.firstWhere((u) => u.id == renterId);
    final owner = _db.users.firstWhere((u) => u.id == product.listedBy, orElse: () => renter);

    final days = endDate.difference(startDate).inDays + 1;
    final totalDays = days <= 0 ? 1 : days;
    final totalPrice = product.price * totalDays;

    // Split Payment Model: 30% upfront deposit, 70% final payment
    final depositAmount = totalPrice * 0.30;
    final finalAmount = totalPrice * 0.70;

    final newBooking = BookingModel(
      id: _db.bookings.length + 1,
      productId: productId,
      renterId: renterId,
      ownerId: owner.id,
      status: 'pending',
      startDate: startDate,
      endDate: endDate,
      totalPrice: totalPrice,
      depositAmount: depositAmount,
      depositPaid: false,
      finalAmount: finalAmount,
      finalPaid: false,
      notes: notes,
      createdAt: DateTime.now(),
      productTitle: product.title,
      productImage: product.imagePath,
      dailyPrice: product.price,
      renterName: renter.fullName,
      ownerName: owner.fullName,
    );

    _db.bookings.insert(0, newBooking);

    // Notify equipment owner
    _db.notifications.insert(
      0,
      NotificationModel(
        id: _db.notifications.length + 1,
        userId: owner.id,
        title: 'New Booking Request',
        message: '${renter.fullName} requested to book "${product.title}" for $totalDays days.',
        type: 'booking',
        relatedId: newBooking.id,
        relatedType: 'booking',
        createdAt: DateTime.now(),
      ),
    );

    return newBooking;
  }

  /// Implements SQL trigger: after_payment_completed
  Future<BookingModel> payDeposit({
    required int bookingId,
    required String paymentMethod,
  }) async {
    await Future.delayed(const Duration(milliseconds: 400));
    final index = _db.bookings.indexWhere((b) => b.id == bookingId);
    if (index == -1) throw Exception('Booking not found');

    final booking = _db.bookings[index];
    final updated = booking.copyWith(
      depositPaid: true,
      depositPaidDate: DateTime.now(),
      paymentStatus: 'pending', // Pending final payment
      paymentMethod: paymentMethod,
      transactionId: 'TXN-${DateTime.now().millisecondsSinceEpoch}',
    );
    _db.bookings[index] = updated;

    // Record payment
    _db.payments.add(
      PaymentModel(
        id: _db.payments.length + 1,
        bookingId: bookingId,
        amount: booking.depositAmount,
        paymentType: 'deposit',
        paymentMethod: paymentMethod,
        transactionId: updated.transactionId!,
        paymentDate: DateTime.now(),
      ),
    );

    // Notify owner
    _db.notifications.insert(
      0,
      NotificationModel(
        id: _db.notifications.length + 1,
        userId: booking.ownerId,
        title: 'Deposit Received (₹${booking.depositAmount.toInt()})',
        message: '30% deposit received for booking #${booking.id} (${booking.productTitle}).',
        type: 'payment',
        relatedId: booking.id,
        relatedType: 'booking',
        createdAt: DateTime.now(),
      ),
    );

    return updated;
  }

  /// Pay the remaining 70% final balance
  Future<BookingModel> payFinal({
    required int bookingId,
    required String paymentMethod,
  }) async {
    await Future.delayed(const Duration(milliseconds: 400));
    final index = _db.bookings.indexWhere((b) => b.id == bookingId);
    if (index == -1) throw Exception('Booking not found');

    final booking = _db.bookings[index];
    final updated = booking.copyWith(
      finalPaid: true,
      finalPaidDate: DateTime.now(),
      paymentStatus: 'paid', // Fully settled
      paymentMethod: paymentMethod,
    );
    _db.bookings[index] = updated;

    // Record payment
    _db.payments.add(
      PaymentModel(
        id: _db.payments.length + 1,
        bookingId: bookingId,
        amount: booking.finalAmount,
        paymentType: 'final',
        paymentMethod: paymentMethod,
        transactionId: 'TXN-${DateTime.now().millisecondsSinceEpoch}',
        paymentDate: DateTime.now(),
      ),
    );

    // Update receipt if exists
    final rIndex = _db.receipts.indexWhere((r) => r.bookingId == bookingId);
    if (rIndex != -1) {
      final old = _db.receipts[rIndex];
      _db.receipts[rIndex] = ReceiptModel(
        id: old.id,
        bookingId: old.bookingId,
        receiptNumber: old.receiptNumber,
        subtotal: old.subtotal,
        taxAmount: old.taxAmount,
        discountAmount: old.discountAmount,
        totalAmount: old.totalAmount,
        depositAmount: old.depositAmount,
        finalAmount: old.finalAmount,
        depositPaid: true,
        finalPaid: true,
        paymentStatus: 'paid',
        paymentDate: DateTime.now(),
        paymentMethod: paymentMethod,
        generatedAt: old.generatedAt,
      );
    }

    return updated;
  }

  /// Implements SQL trigger: after_booking_confirmed
  Future<BookingModel> confirmBooking(int bookingId) async {
    await Future.delayed(const Duration(milliseconds: 250));
    final index = _db.bookings.indexWhere((b) => b.id == bookingId);
    if (index == -1) throw Exception('Booking not found');

    final booking = _db.bookings[index];
    final updated = booking.copyWith(status: 'confirmed');
    _db.bookings[index] = updated;

    // Generate receipt if not already exists
    final receiptExists = _db.receipts.any((r) => r.bookingId == bookingId);
    if (!receiptExists) {
      _db.receipts.add(
        ReceiptModel.fromBooking(
          id: _db.receipts.length + 1,
          bookingId: bookingId,
          totalPrice: updated.totalPrice,
          depositAmount: updated.depositAmount,
          finalAmount: updated.finalAmount,
          depositPaid: updated.depositPaid,
          finalPaid: updated.finalPaid,
          paymentMethod: updated.paymentMethod,
        ),
      );
    }

    // Notify renter
    _db.notifications.insert(
      0,
      NotificationModel(
        id: _db.notifications.length + 1,
        userId: booking.renterId,
        title: 'Booking Confirmed!',
        message: 'Owner confirmed your booking for ${booking.productTitle}.',
        type: 'booking',
        relatedId: booking.id,
        relatedType: 'booking',
        createdAt: DateTime.now(),
      ),
    );

    return updated;
  }

  Future<BookingModel> updateBookingStatus(int bookingId, String status) async {
    await Future.delayed(const Duration(milliseconds: 200));
    final index = _db.bookings.indexWhere((b) => b.id == bookingId);
    if (index == -1) throw Exception('Booking not found');

    final booking = _db.bookings[index];
    final updated = booking.copyWith(status: status);
    _db.bookings[index] = updated;
    return updated;
  }
}
