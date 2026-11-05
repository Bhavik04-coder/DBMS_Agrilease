import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/notifications_provider.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.currentUser;
    final notifProvider = context.watch<NotificationsProvider>();
    final list = notifProvider.notifications;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (list.isNotEmpty && user != null)
            TextButton(
              onPressed: () => notifProvider.markAllAsRead(user.id),
              child: const Text('Mark all read', style: TextStyle(color: Colors.white)),
            ),
        ],
      ),
      body: list.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.notifications_none_rounded, size: 64, color: Colors.grey),
                  SizedBox(height: 12),
                  Text('No notifications', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  SizedBox(height: 4),
                  Text('Booking and payment updates will appear here', style: TextStyle(color: Colors.grey, fontSize: 13)),
                ],
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (context, index) {
                final n = list[index];
                return Dismissible(
                  key: Key(n.id.toString()),
                  onDismissed: (_) {
                    if (user != null) notifProvider.markAsRead(n.id, user.id);
                  },
                  child: Card(
                    color: n.isRead ? Colors.white : const Color(0xFFF1F8E9),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      leading: CircleAvatar(
                        backgroundColor: _getTypeColor(n.type).withOpacity(0.15),
                        child: Icon(_getTypeIcon(n.type), color: _getTypeColor(n.type), size: 20),
                      ),
                      title: Text(
                        n.title,
                        style: TextStyle(
                          fontWeight: n.isRead ? FontWeight.normal : FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: 4),
                          Text(n.message, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                          const SizedBox(height: 6),
                          Text(
                            Formatters.formatDateTime(n.createdAt),
                            style: const TextStyle(fontSize: 10, color: Colors.grey),
                          ),
                        ],
                      ),
                      trailing: n.isRead
                          ? null
                          : Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(
                                color: AppTheme.primaryGreen,
                                shape: BoxShape.circle,
                              ),
                            ),
                      onTap: () {
                        if (user != null) notifProvider.markAsRead(n.id, user.id);
                      },
                    ),
                  ),
                );
              },
            ),
    );
  }

  Color _getTypeColor(String type) {
    switch (type) {
      case 'payment':
        return AppTheme.primaryDarkGreen;
      case 'booking':
        return const Color(0xFF1976D2);
      default:
        return const Color(0xFFE65100);
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type) {
      case 'payment':
        return Icons.payment_rounded;
      case 'booking':
        return Icons.event_available_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }
}
