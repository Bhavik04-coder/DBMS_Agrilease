import 'package:flutter/material.dart';
import '../data/models/notification_model.dart';
import '../data/repositories/notification_repository.dart';

class NotificationsProvider extends ChangeNotifier {
  final NotificationRepository _repo = NotificationRepository();

  List<NotificationModel> _notifications = [];
  bool _isLoading = false;

  List<NotificationModel> get notifications => _notifications;
  bool get isLoading => _isLoading;
  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  Future<void> loadNotifications(int userId) async {
    _isLoading = true;
    notifyListeners();

    _notifications = await _repo.getNotifications(userId);
    _isLoading = false;
    notifyListeners();
  }

  Future<void> markAsRead(int id, int userId) async {
    await _repo.markAsRead(id);
    await loadNotifications(userId);
  }

  Future<void> markAllAsRead(int userId) async {
    await _repo.markAllAsRead(userId);
    await loadNotifications(userId);
  }
}
