import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../data/models/product_model.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/custom_button.dart';
import '../../common/custom_text_field.dart';
import 'booking_confirmed_screen.dart';

class BookProductScreen extends StatefulWidget {
  final ProductModel product;

  const BookProductScreen({super.key, required this.product});

  @override
  State<BookProductScreen> createState() => _BookProductScreenState();
}

class _BookProductScreenState extends State<BookProductScreen> {
  DateTime _startDate = DateTime.now().add(const Duration(days: 1));
  DateTime _endDate = DateTime.now().add(const Duration(days: 3));
  final _notesController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  int get _rentalDays {
    final diff = _endDate.difference(_startDate).inDays;
    return diff <= 0 ? 1 : diff + 1;
  }

  double get _totalPrice => widget.product.price * _rentalDays;
  double get _depositAmount => _totalPrice * 0.30;
  double get _finalAmount => _totalPrice * 0.70;

  Future<void> _selectDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 180)),
      initialDateRange: DateTimeRange(start: _startDate, end: _endDate),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppTheme.primaryGreen,
              onPrimary: Colors.white,
              onSurface: AppTheme.textPrimary,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
    }
  }

  void _submitBooking() async {
    final auth = context.read<AuthProvider>();
    final user = auth.currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    final bookingsProvider = context.read<BookingsProvider>();
    final booking = await bookingsProvider.createBooking(
      productId: widget.product.id,
      renterId: user.id,
      startDate: _startDate,
      endDate: _endDate,
      notes: _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
    );

    setState(() => _isLoading = false);

    if (booking != null && mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => BookingConfirmedScreen(booking: booking),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Book Agricultural Machinery'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Selected Equipment Card
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE0E0E0)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryGreen.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.agriculture_rounded, color: AppTheme.primaryGreen, size: 32),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.product.title,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        Text(
                          '${widget.product.category} • ${widget.product.location}',
                          style: const TextStyle(color: AppTheme.textSecondary, fontSize: 12),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${Formatters.formatCurrency(widget.product.price)} / day',
                          style: const TextStyle(
                            color: AppTheme.primaryDarkGreen,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Date Range Selection Box
            const Text(
              'Rental Duration',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            InkWell(
              onTap: _selectDateRange,
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppTheme.primaryGreen, width: 1.5),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today_rounded, color: AppTheme.primaryGreen, size: 24),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${Formatters.formatDate(_startDate)}  →  ${Formatters.formatDate(_endDate)}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Total Period: ${Formatters.formatDuration(_rentalDays)}',
                            style: const TextStyle(color: AppTheme.primaryGreen, fontWeight: FontWeight.w600, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.edit_calendar_rounded, color: AppTheme.textSecondary),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Split Payment Calculation Card (Matching SQL Triggers)
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F8E9),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFC8E6C9)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.receipt_long_rounded, color: AppTheme.primaryDarkGreen, size: 20),
                      SizedBox(width: 8),
                      Text(
                        'AgriLease Transparent Split Payment',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryDarkGreen,
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  _buildPriceRow(
                    'Daily Rate × $_rentalDays days',
                    Formatters.formatCurrency(_totalPrice),
                  ),
                  const SizedBox(height: 10),
                  _buildPriceRow(
                    '1. Upfront Deposit (30% to reserve)',
                    Formatters.formatCurrency(_depositAmount),
                    isHighlight: true,
                    highlightColor: AppTheme.primaryDarkGreen,
                  ),
                  const SizedBox(height: 6),
                  _buildPriceRow(
                    '2. Final Balance (70% on handover)',
                    Formatters.formatCurrency(_finalAmount),
                    isHighlight: false,
                  ),
                  const Divider(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Total Estimated Hire',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      Text(
                        Formatters.formatCurrency(_totalPrice),
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 20,
                          color: AppTheme.primaryDarkGreen,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            CustomTextField(
              controller: _notesController,
              label: 'Additional Farm Notes or Field Location (Optional)',
              hint: 'e.g. Please deliver to North Field near canal gate 3.',
              prefixIcon: Icons.notes_rounded,
              maxLines: 2,
            ),
            const SizedBox(height: 30),

            SizedBox(
              width: double.infinity,
              child: CustomButton(
                text: 'Confirm & Reserve with 30% Deposit',
                icon: Icons.check_circle_rounded,
                isLoading: _isLoading,
                onPressed: _submitBooking,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPriceRow(
    String label,
    String value, {
    bool isHighlight = false,
    Color? highlightColor,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            color: isHighlight ? (highlightColor ?? AppTheme.textPrimary) : AppTheme.textSecondary,
            fontWeight: isHighlight ? FontWeight.bold : FontWeight.normal,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: isHighlight ? FontWeight.bold : FontWeight.w600,
            color: highlightColor ?? AppTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}
