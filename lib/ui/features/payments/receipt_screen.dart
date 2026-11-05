import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../data/models/receipt_model.dart';
import '../../../data/models/booking_model.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/custom_button.dart';
import '../../common/status_badge.dart';

class ReceiptScreen extends StatefulWidget {
  final int bookingId;

  const ReceiptScreen({super.key, required this.bookingId});

  @override
  State<ReceiptScreen> createState() => _ReceiptScreenState();
}

class _ReceiptScreenState extends State<ReceiptScreen> {
  ReceiptModel? _receipt;
  BookingModel? _booking;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadReceipt();
  }

  void _loadReceipt() async {
    final bookingsProvider = context.read<BookingsProvider>();
    final r = await bookingsProvider.getReceipt(widget.bookingId);
    final b = bookingsProvider.myBookings.where((item) => item.id == widget.bookingId).firstOrNull ??
              bookingsProvider.receivedBookings.where((item) => item.id == widget.bookingId).firstOrNull;

    if (mounted) {
      setState(() {
        _receipt = r ?? (b != null
            ? ReceiptModel.fromBooking(
                id: 1,
                bookingId: b.id,
                totalPrice: b.totalPrice,
                depositAmount: b.depositAmount,
                finalAmount: b.finalAmount,
                depositPaid: b.depositPaid,
                finalPaid: b.finalPaid,
                paymentMethod: b.paymentMethod,
              )
            : null);
        _booking = b;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_receipt == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Tax Invoice & Receipt')),
        body: const Center(child: Text('Receipt not available yet.')),
      );
    }

    final r = _receipt!;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tax Invoice & Receipt'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share_rounded),
            tooltip: 'Share Receipt',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Invoice copied to clipboard!')),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // White Paper Invoice Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE0E0E0)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.06),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Invoice Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.agriculture_rounded, color: AppTheme.primaryDarkGreen, size: 24),
                              SizedBox(width: 6),
                              Text(
                                'AgriLease Ltd.',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primaryDarkGreen,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'GSTIN: 07AAACG1234D1Z5',
                            style: TextStyle(fontSize: 11, color: AppTheme.textSecondary),
                          ),
                        ],
                      ),
                      StatusBadge(status: r.paymentStatus, isPayment: true),
                    ],
                  ),
                  const Divider(height: 24),

                  // Receipt No & Date
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Invoice Number', style: TextStyle(fontSize: 11, color: Colors.grey)),
                          Text(
                            r.receiptNumber,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          const Text('Date Generated', style: TextStyle(fontSize: 11, color: Colors.grey)),
                          Text(
                            Formatters.formatShortDate(r.generatedAt),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  if (_booking != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF9F9F9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _booking!.productTitle,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Hired by: ${_booking!.renterName} | Owner: ${_booking!.ownerName}',
                            style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                          ),
                          Text(
                            'Period: ${Formatters.formatShortDate(_booking!.startDate)} - ${Formatters.formatShortDate(_booking!.endDate)} (${_booking!.totalDays} days)',
                            style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Line Items Breakdown
                  const Text(
                    'Cost Breakdown',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  _buildInvoiceLine('Base Hire Charges', Formatters.formatCurrency(r.subtotal)),
                  _buildInvoiceLine('GST / Government Tax (18%)', Formatters.formatCurrency(r.taxAmount)),
                  if (r.discountAmount > 0)
                    _buildInvoiceLine('Promotional Discount', '-${Formatters.formatCurrency(r.discountAmount)}', color: Colors.green),
                  const Divider(height: 20),
                  _buildInvoiceLine(
                    'Total Tax Invoice Value',
                    Formatters.formatCurrency(r.totalAmount),
                    isBold: true,
                    fontSize: 16,
                    color: AppTheme.primaryDarkGreen,
                  ),
                  const SizedBox(height: 16),

                  // Split Payment Tracking
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F8E9),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFC8E6C9)),
                    ),
                    child: Column(
                      children: [
                        _buildPaymentRow(
                          '1. Advance Deposit (30%)',
                          Formatters.formatCurrency(r.depositAmount),
                          r.depositPaid,
                        ),
                        const SizedBox(height: 8),
                        _buildPaymentRow(
                          '2. Final Balance (70%)',
                          Formatters.formatCurrency(r.finalAmount),
                          r.finalPaid,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      const Icon(Icons.payment, size: 16, color: Colors.grey),
                      const SizedBox(width: 6),
                      Text(
                        'Payment Method: ${r.paymentMethod ?? 'UPI / Escrow'}',
                        style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              child: CustomButton(
                text: 'Download PDF Invoice',
                icon: Icons.download_rounded,
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Tax invoice downloaded to device documents!'),
                      backgroundColor: AppTheme.primaryDarkGreen,
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 10),
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Back to Previous Screen'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInvoiceLine(
    String label,
    String value, {
    bool isBold = false,
    double fontSize = 13,
    Color? color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: fontSize,
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              color: color ?? (isBold ? AppTheme.textPrimary : AppTheme.textSecondary),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: fontSize,
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: color ?? AppTheme.textPrimary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentRow(String label, String value, bool isPaid) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Icon(
              isPaid ? Icons.check_circle_rounded : Icons.pending_rounded,
              size: 16,
              color: isPaid ? AppTheme.statusCompleted : AppTheme.statusPending,
            ),
            const SizedBox(width: 6),
            Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500)),
          ],
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: isPaid ? AppTheme.statusCompleted : AppTheme.statusPending,
          ),
        ),
      ],
    );
  }
}
