import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../data/models/booking_model.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/custom_button.dart';
import 'receipt_screen.dart';

class PaymentScreen extends StatefulWidget {
  final BookingModel booking;
  final bool isDeposit; // true for 30% deposit, false for 70% final

  const PaymentScreen({
    super.key,
    required this.booking,
    this.isDeposit = true,
  });

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  String _selectedMethod = 'upi';
  bool _isProcessing = false;

  double get _amountToPay => widget.isDeposit ? widget.booking.depositAmount : widget.booking.finalAmount;
  String get _paymentTitle => widget.isDeposit ? '30% Advance Deposit' : '70% Final Balance Payment';

  void _processPayment() async {
    final auth = context.read<AuthProvider>();
    final user = auth.currentUser;
    if (user == null) return;

    setState(() => _isProcessing = true);

    final bookingsProvider = context.read<BookingsProvider>();
    bool success;

    if (widget.isDeposit) {
      success = await bookingsProvider.payDeposit(
        bookingId: widget.booking.id,
        paymentMethod: _selectedMethod.toUpperCase(),
        renterId: user.id,
      );
    } else {
      success = await bookingsProvider.payFinal(
        bookingId: widget.booking.id,
        paymentMethod: _selectedMethod.toUpperCase(),
        renterId: user.id,
      );
    }

    setState(() => _isProcessing = false);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Payment of ${Formatters.formatCurrency(_amountToPay)} successful!'),
          backgroundColor: AppTheme.primaryDarkGreen,
        ),
      );

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => ReceiptScreen(bookingId: widget.booking.id),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Secure Payment Checkout'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Amount Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryDarkGreen, AppTheme.primaryGreen],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  Text(
                    _paymentTitle,
                    style: const TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    Formatters.formatCurrency(_amountToPay),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'For Booking #AGR-${widget.booking.id} (${widget.booking.productTitle})',
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            const Text(
              'Select Payment Mode',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),

            // Payment Options
            ...AppConstants.paymentMethods.map((method) {
              final isSelected = _selectedMethod == method['id'];
              return Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: InkWell(
                  onTap: () => setState(() => _selectedMethod = method['id'] as String),
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isSelected ? AppTheme.primaryGreen : const Color(0xFFCFD8DC),
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          method['icon'] as IconData,
                          size: 28,
                          color: isSelected ? AppTheme.primaryGreen : Colors.grey[600],
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                method['title'] as String,
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                  color: isSelected ? AppTheme.primaryDarkGreen : AppTheme.textPrimary,
                                ),
                              ),
                              Text(
                                method['description'] as String,
                                style: const TextStyle(color: AppTheme.textSecondary, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        Icon(
                          isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                          color: isSelected ? AppTheme.primaryGreen : Colors.grey,
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }).toList(),
            const SizedBox(height: 20),

            // Split Payment Assurance Note
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFE8F5E9),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Row(
                children: [
                  Icon(Icons.verified_user_rounded, color: AppTheme.primaryGreen, size: 20),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Protected by AgriLease Escrow. Deposit is held securely until machinery is inspected and handed over.',
                      style: TextStyle(fontSize: 11, color: AppTheme.primaryDarkGreen),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 30),

            SizedBox(
              width: double.infinity,
              child: CustomButton(
                text: 'Pay ${Formatters.formatCurrency(_amountToPay)} Now',
                icon: Icons.lock_outline_rounded,
                isLoading: _isProcessing,
                onPressed: _processPayment,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
