import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final bool isPayment;

  const StatusBadge({
    super.key,
    required this.status,
    this.isPayment = false,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color text;
    String display = status.toUpperCase();

    final s = status.toLowerCase();

    if (isPayment) {
      if (s == 'paid') {
        bg = AppTheme.statusCompleted.withOpacity(0.12);
        text = AppTheme.statusCompleted;
        display = 'PAID (100%)';
      } else if (s == 'partial') {
        bg = AppTheme.statusPending.withOpacity(0.12);
        text = AppTheme.statusPending;
        display = 'DEPOSIT PAID (30%)';
      } else {
        bg = const Color(0xFFEEEEEE);
        text = const Color(0xFF616161);
        display = 'PAYMENT PENDING';
      }
    } else {
      switch (s) {
        case 'confirmed':
          bg = AppTheme.statusConfirmed.withOpacity(0.12);
          text = AppTheme.statusConfirmed;
          break;
        case 'completed':
          bg = AppTheme.statusCompleted.withOpacity(0.12);
          text = AppTheme.statusCompleted;
          break;
        case 'cancelled':
          bg = AppTheme.statusCancelled.withOpacity(0.12);
          text = AppTheme.statusCancelled;
          break;
        case 'pending':
        default:
          bg = AppTheme.statusPending.withOpacity(0.12);
          text = AppTheme.statusPending;
          break;
      }
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: text.withOpacity(0.3), width: 1),
      ),
      child: Text(
        display,
        style: TextStyle(
          color: text,
          fontSize: 11,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}
