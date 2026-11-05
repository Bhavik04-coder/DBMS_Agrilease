import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';
import '../../core/animations/pulse_animation.dart';

class AnimatedStatusBadge extends StatelessWidget {
  final String status;
  final TextStyle? textStyle;
  final EdgeInsets padding;

  const AnimatedStatusBadge({
    super.key,
    required this.status,
    this.textStyle,
    this.padding = const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
  });

  Color _getStatusColor() {
    switch (status.toLowerCase()) {
      case 'pending':
        return AppTheme.statusPending;
      case 'confirmed':
        return AppTheme.statusConfirmed;
      case 'completed':
        return AppTheme.statusCompleted;
      case 'cancelled':
        return AppTheme.statusCancelled;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon() {
    switch (status.toLowerCase()) {
      case 'pending':
        return Icons.schedule;
      case 'confirmed':
        return Icons.check_circle;
      case 'completed':
        return Icons.done_all;
      case 'cancelled':
        return Icons.cancel;
      default:
        return Icons.help;
    }
  }

  bool _shouldPulse() => status.toLowerCase() == 'pending';

  Widget _buildBadge() {
    final color = _getStatusColor();
    final icon = _getStatusIcon();

    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color, width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 6),
          Text(
            status,
            style: textStyle ??
                TextStyle(
                  color: color,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final badge = _buildBadge();
    
    if (_shouldPulse()) {
      return PulseAnimation(
        child: badge,
        minOpacity: 0.6,
        maxOpacity: 1.0,
      );
    }
    
    return badge;
  }
}
