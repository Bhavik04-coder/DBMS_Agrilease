import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/custom_button.dart';
import '../../common/status_badge.dart';
import '../payments/payment_screen.dart';
import '../payments/receipt_screen.dart';

class MyBookingsScreen extends StatefulWidget {
  const MyBookingsScreen({super.key});

  @override
  State<MyBookingsScreen> createState() => _MyBookingsScreenState();
}

class _MyBookingsScreenState extends State<MyBookingsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().currentUser;
      if (user != null) {
        context.read<BookingsProvider>().loadUserBookings(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final bookingsProvider = context.watch<BookingsProvider>();
    final bookings = bookingsProvider.myBookings;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Machinery Bookings'),
      ),
      body: bookingsProvider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : bookings.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.calendar_month_outlined, size: 70, color: Colors.grey),
                        const SizedBox(height: 16),
                        const Text(
                          'No Equipment Booked Yet',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Browse the machinery catalog and rent tractors, seeders or tillers for your fields.',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: bookings.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 14),
                  itemBuilder: (context, index) {
                    final b = bookings[index];
                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Header with Status
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    b.productTitle,
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                  ),
                                ),
                                StatusBadge(status: b.status),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Owner: ${b.ownerName} • Booking #AGR-${b.id}',
                              style: const TextStyle(color: AppTheme.textSecondary, fontSize: 12),
                            ),
                            const SizedBox(height: 12),

                            // Dates & Duration
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF5F5F5),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.date_range, size: 16, color: AppTheme.primaryGreen),
                                      const SizedBox(width: 6),
                                      Text(
                                        '${Formatters.formatShortDate(b.startDate)} to ${Formatters.formatShortDate(b.endDate)}',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                  Text(
                                    Formatters.formatDuration(b.totalDays),
                                    style: const TextStyle(fontSize: 12, color: AppTheme.primaryDarkGreen, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 14),

                            // Payment Breakdown Table
                            Row(
                              children: [
                                Expanded(
                                  child: _buildPaymentStatusTile(
                                    '1. Deposit (30%)',
                                    Formatters.formatCurrency(b.depositAmount),
                                    b.depositPaid,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: _buildPaymentStatusTile(
                                    '2. Balance (70%)',
                                    Formatters.formatCurrency(b.finalAmount),
                                    b.finalPaid,
                                  ),
                                ),
                              ],
                            ),
                            const Divider(height: 22),

                            // Bottom Actions
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('Total Hire Cost', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                    Text(
                                      Formatters.formatCurrency(b.totalPrice),
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryDarkGreen,
                                      ),
                                    ),
                                  ],
                                ),
                                Row(
                                  children: [
                                    // Tax Receipt Button
                                    if (b.status == 'confirmed' || b.depositPaid)
                                      OutlinedButton.icon(
                                        icon: const Icon(Icons.receipt_long, size: 16),
                                        label: const Text('Receipt'),
                                        style: OutlinedButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                        ),
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) => ReceiptScreen(bookingId: b.id),
                                            ),
                                          );
                                        },
                                      ),
                                    const SizedBox(width: 8),

                                    // Deposit Pay CTA
                                    if (!b.depositPaid)
                                      ElevatedButton(
                                        style: ElevatedButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                        ),
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) => PaymentScreen(booking: b, isDeposit: true),
                                            ),
                                          );
                                        },
                                        child: const Text('Pay Deposit'),
                                      )
                                    // Final 70% Pay CTA
                                    else if (!b.finalPaid && b.status == 'confirmed')
                                      ElevatedButton(
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFF1976D2),
                                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                        ),
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) => PaymentScreen(booking: b, isDeposit: false),
                                            ),
                                          );
                                        },
                                        child: const Text('Pay Balance'),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }

  Widget _buildPaymentStatusTile(String title, String amount, bool isPaid) {
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: isPaid ? const Color(0xFFE8F5E9) : const Color(0xFFFFF3E0),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isPaid ? const Color(0xFFC8E6C9) : const Color(0xFFFFE0B2),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                isPaid ? Icons.check_circle_rounded : Icons.hourglass_top_rounded,
                size: 14,
                color: isPaid ? AppTheme.statusCompleted : AppTheme.statusPending,
              ),
              const SizedBox(width: 4),
              Text(
                isPaid ? 'PAID' : 'DUE',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: isPaid ? AppTheme.statusCompleted : AppTheme.statusPending,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(title, style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
          Text(amount, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}
