import '../models/notification_model.dart';
import '../services/mock_database.dart';

class NotificationRepository {
  final MockDatabase _db = MockDatabase();

  Future<List<NotificationModel>> getNotifications(int userId) async {
    await Future.delayed(const Duration(milliseconds: 100));
    return _db.notifications.where((n) => n.userId == userId).toList();
  }

  Future<void> markAsRead(int notificationId) async {
    final index = _db.notifications.indexWhere((n) => n.id == notificationId);
    if (index != -1) {
      final old = _db.notifications[index];
      _db.notifications[index] = old.copyWith(isRead: true);
    }
  }

  Future<void> markAllAsRead(int userId) async {
    for (int i = 0; i < _db.notifications.length; i++) {
      if (_db.notifications[i].userId == userId) {
        _db.notifications[i] = _db.notifications[i].copyWith(isRead: true);
      }
    }
  }

  int getUnreadCount(int userId) {
    return _db.notifications.where((n) => n.userId == userId && !n.isRead).length;
  }
}
