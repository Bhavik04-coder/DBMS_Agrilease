import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../data/models/booking_model.dart';
import '../../common/custom_button.dart';
import '../payments/payment_screen.dart';
import '../navigation/main_navigation_screen.dart';

class BookingConfirmedScreen extends StatelessWidget {
  final BookingModel booking;

  const BookingConfirmedScreen({super.key, required this.booking});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Booking Request Submitted'),
        automaticallyImplyLeading: false,
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Success checkmark badge
              Container(
                width: 80,
                height: 80,
                decoration: const BoxDecoration(
                  color: Color(0xFFE8F5E9),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_circle_rounded,
                  color: AppTheme.primaryDarkGreen,
                  size: 52,
                ),
              ),
              const SizedBox(height: 18),
              const Text(
                'Booking Request Placed!',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDarkGreen,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'Booking Reference: #AGR-${booking.id.toString().padLeft(6, '0')}',
                style: const TextStyle(
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary,
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 24),

              // Summary Box
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE0E0E0)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 10,
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    _buildSummaryRow('Equipment', booking.productTitle),
                    const Divider(height: 18),
                    _buildSummaryRow(
                      'Dates',
                      '${Formatters.formatShortDate(booking.startDate)} to ${Formatters.formatShortDate(booking.endDate)}',
                    ),
                    const Divider(height: 18),
                    _buildSummaryRow('Total Days', Formatters.formatDuration(booking.totalDays)),
                    const Divider(height: 18),
                    _buildSummaryRow('Total Cost', Formatters.formatCurrency(booking.totalPrice)),
                    const Divider(height: 18),
                    _buildSummaryRow(
                      '30% Deposit Due',
                      Formatters.formatCurrency(booking.depositAmount),
                      isBold: true,
                      color: AppTheme.primaryDarkGreen,
                    ),
                    const Divider(height: 18),
                    _buildSummaryRow(
                      '70% Final Balance',
                      Formatters.formatCurrency(booking.finalAmount),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              SizedBox(
                width: double.infinity,
                child: CustomButton(
                  text: 'Pay 30% Deposit Now (${Formatters.formatCurrency(booking.depositAmount)})',
                  icon: Icons.payment_rounded,
                  onPressed: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (_) => PaymentScreen(
                          booking: booking,
                          isDeposit: true,
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 12),

              SizedBox(
                width: double.infinity,
                child: CustomButton(
                  text: 'View in My Bookings',
                  isOutlined: true,
                  onPressed: () {
                    Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const MainNavigationScreen(initialIndex: 2),
                      ),
                      (route) => false,
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value, {bool isBold = false, Color? color}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
            color: color ?? AppTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}
