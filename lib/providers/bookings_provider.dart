import 'package:flutter/material.dart';
import '../data/models/booking_model.dart';
import '../data/models/receipt_model.dart';
import '../data/repositories/booking_repository.dart';

class BookingsProvider extends ChangeNotifier {
  final BookingRepository _repo = BookingRepository();

  List<BookingModel> _myBookings = [];
  List<BookingModel> _receivedBookings = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<BookingModel> get myBookings => _myBookings;
  List<BookingModel> get receivedBookings => _receivedBookings;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> loadUserBookings(int renterId) async {
    _isLoading = true;
    notifyListeners();

    try {
      _myBookings = await _repo.getUserBookings(renterId);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  Future<void> loadReceivedBookings(int ownerId) async {
    _isLoading = true;
    notifyListeners();

    try {
      _receivedBookings = await _repo.getOwnerBookings(ownerId);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  Future<BookingModel?> createBooking({
    required int productId,
    required int renterId,
    required DateTime startDate,
    required DateTime endDate,
    String? notes,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final booking = await _repo.createBooking(
        productId: productId,
        renterId: renterId,
        startDate: startDate,
        endDate: endDate,
        notes: notes,
      );
      await loadUserBookings(renterId);
      _isLoading = false;
      notifyListeners();
      return booking;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
      return null;
    }
  }

  Future<bool> payDeposit({
    required int bookingId,
    required String paymentMethod,
    required int renterId,
  }) async {
    try {
      await _repo.payDeposit(bookingId: bookingId, paymentMethod: paymentMethod);
      await loadUserBookings(renterId);
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> payFinal({
    required int bookingId,
    required String paymentMethod,
    required int renterId,
  }) async {
    try {
      await _repo.payFinal(bookingId: bookingId, paymentMethod: paymentMethod);
      await loadUserBookings(renterId);
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> confirmBooking(int bookingId, int ownerId) async {
    try {
      await _repo.confirmBooking(bookingId);
      await loadReceivedBookings(ownerId);
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateStatus(int bookingId, String status, int ownerId) async {
    try {
      await _repo.updateBookingStatus(bookingId, status);
      await loadReceivedBookings(ownerId);
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<ReceiptModel?> getReceipt(int bookingId) async {
    return await _repo.getReceiptForBooking(bookingId);
  }
}
