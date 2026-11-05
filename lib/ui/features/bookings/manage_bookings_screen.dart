import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/status_badge.dart';
import '../payments/receipt_screen.dart';

class ManageBookingsScreen extends StatefulWidget {
  const ManageBookingsScreen({super.key});

  @override
  State<ManageBookingsScreen> createState() => _ManageBookingsScreenState();
}

class _ManageBookingsScreenState extends State<ManageBookingsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().currentUser;
      if (user != null) {
        context.read<BookingsProvider>().loadReceivedBookings(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.currentUser;
    final bookingsProvider = context.watch<BookingsProvider>();
    final bookings = bookingsProvider.receivedBookings;

    if (user == null) {
      return const Scaffold(body: Center(child: Text('Please log in')));
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Received Hire Requests'),
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
                        const Icon(Icons.inbox_rounded, size: 70, color: Colors.grey),
                        const SizedBox(height: 16),
                        const Text(
                          'No Hire Requests Yet',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'When other farmers request to hire your tractors or machinery, requests will appear here for your confirmation.',
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
                            const SizedBox(height: 6),
                            Text(
                              'Renter: ${b.renterName} • Booking #AGR-${b.id}',
                              style: const TextStyle(color: AppTheme.textSecondary, fontSize: 12),
                            ),
                            if (b.notes != null && b.notes!.isNotEmpty) ...[
                              const SizedBox(height: 8),
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F8E9),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'Note: "${b.notes}"',
                                  style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
                                ),
                              ),
                            ],
                            const SizedBox(height: 12),

                            // Dates & Payment Status Summary
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('Rental Period', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                    Text(
                                      '${Formatters.formatShortDate(b.startDate)} - ${Formatters.formatShortDate(b.endDate)} (${b.totalDays}d)',
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                    ),
                                  ],
                                ),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    const Text('Deposit (30%)', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                    Text(
                                      b.depositPaid ? 'RECEIVED (₹${b.depositAmount.toInt()})' : 'AWAITING (₹${b.depositAmount.toInt()})',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: b.depositPaid ? AppTheme.statusCompleted : AppTheme.statusPending,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const Divider(height: 22),

                            // Actions
                            Row(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                if (b.status == 'confirmed') ...[
                                  OutlinedButton.icon(
                                    icon: const Icon(Icons.receipt_long, size: 16),
                                    label: const Text('Invoice'),
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
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppTheme.statusCompleted,
                                    ),
                                    onPressed: () {
                                      bookingsProvider.updateStatus(b.id, 'completed', user.id);
                                    },
                                    child: const Text('Mark Completed'),
                                  ),
                                ] else if (b.status == 'pending') ...[
                                  OutlinedButton(
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: Colors.red,
                                      side: const BorderSide(color: Colors.red),
                                    ),
                                    onPressed: () {
                                      bookingsProvider.updateStatus(b.id, 'cancelled', user.id);
                                    },
                                    child: const Text('Decline'),
                                  ),
                                  const SizedBox(width: 8),
                                  ElevatedButton(
                                    onPressed: () {
                                      bookingsProvider.confirmBooking(b.id, user.id);
                                    },
                                    child: const Text('Confirm Request'),
                                  ),
                                ] else ...[
                                  Text(
                                    'Order ${b.status.toUpperCase()}',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey),
                                  ),
                                ],
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
}
