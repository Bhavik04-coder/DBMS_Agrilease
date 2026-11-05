import 'package:flutter_test/flutter_test.dart';
import 'package:agrilease/data/models/booking_model.dart';
import 'package:agrilease/data/repositories/booking_repository.dart';

void main() {
  group('AgriLease DBMS Business Logic & Split Payment Tests', () {
    final repository = BookingRepository();

    test('Booking duration and 30% deposit calculation matches SQL trigger before_booking_insert', () async {
      final start = DateTime(2026, 11, 10);
      final end = DateTime(2026, 11, 12); // 3 days inclusive

      final booking = await repository.createBooking(
        productId: 1, // John Deere Tractor, ₹1,500/day
        renterId: 2,
        startDate: start,
        endDate: end,
      );

      expect(booking.totalDays, 3);
      expect(booking.totalPrice, 4500.0);
      expect(booking.depositAmount, 1350.0); // 30% of 4,500
      expect(booking.finalAmount, 3150.0);   // 70% of 4,500
      expect(booking.depositPaid, false);
      expect(booking.finalPaid, false);
    });

    test('Paying deposit updates payment status matching SQL trigger after_payment_completed', () async {
      final booking = await repository.payDeposit(
        bookingId: 1,
        paymentMethod: 'UPI',
      );

      expect(booking.depositPaid, true);
      expect(booking.depositPaidDate, isNotNull);
      expect(booking.paymentMethod, 'UPI');
    });

    test('Paying final balance marks booking fully settled', () async {
      final booking = await repository.payFinal(
        bookingId: 1,
        paymentMethod: 'UPI',
      );

      expect(booking.finalPaid, true);
      expect(booking.finalPaidDate, isNotNull);
      expect(booking.paymentStatus, 'paid');
    });

    test('Confirming booking generates receipt with RCP- prefix matching SQL trigger after_booking_confirmed', () async {
      await repository.confirmBooking(1);
      final receipt = await repository.getReceiptForBooking(1);

      expect(receipt, isNotNull);
      expect(receipt!.receiptNumber.startsWith('RCP-'), true);
      expect(receipt.taxAmount, closeTo(receipt.subtotal * 0.18, 0.01));
    });
  });
}
