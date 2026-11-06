import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/user_model.dart';
import '../models/product_model.dart';
import '../models/booking_model.dart';

class ApiService {
  // Change this based on where your backend is running
  static const String baseUrl = 'http://localhost/DBMS_Agrilease/api';
  
  // For Android emulator or device on network:
  // static const String baseUrl = 'http://192.168.x.x/DBMS_Agrilease/api';
  
  final http.Client _client = http.Client();
  static const Duration _timeout = Duration(seconds: 30);

  /// Login API Call
  Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final response = await _client
          .post(
            Uri.parse('$baseUrl/auth/login.php'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({'username': username, 'password': password}),
          )
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          return data;
        }
        throw Exception(data['message'] ?? 'Login failed');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Login error: $e');
    }
  }

  /// Fetch all products
  Future<List<ProductModel>> fetchProducts({
    String? category,
    String? search,
  }) async {
    try {
      String url = '$baseUrl/products/fetch.php';
      
      final params = <String, String>{};
      if (category != null && category.isNotEmpty) params['category'] = category;
      if (search != null && search.isNotEmpty) params['search'] = search;
      
      if (params.isNotEmpty) {
        url += '?${params.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&')}';
      }

      final response = await _client
          .get(Uri.parse(url))
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          final products = (data['products'] as List)
              .map((p) => ProductModel.fromJson(p as Map<String, dynamic>))
              .toList();
          return products;
        }
        throw Exception(data['message'] ?? 'Failed to fetch products');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Fetch products error: $e');
    }
  }

  /// Fetch single product
  Future<ProductModel?> fetchProductById(int id) async {
    try {
      final response = await _client
          .get(Uri.parse('$baseUrl/products/detail.php?id=$id'))
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          return ProductModel.fromJson(data['product'] as Map<String, dynamic>);
        }
      }
      return null;
    } catch (e) {
      throw Exception('Fetch product error: $e');
    }
  }

  /// Book equipment
  Future<BookingModel?> bookEquipment(Map<String, dynamic> payload) async {
    try {
      final response = await _client
          .post(
            Uri.parse('$baseUrl/bookings/create.php'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode(payload),
          )
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          return BookingModel.fromJson(data['booking'] as Map<String, dynamic>);
        }
        throw Exception(data['message'] ?? 'Booking failed');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Booking error: $e');
    }
  }

  /// Fetch user bookings
  Future<List<BookingModel>> fetchUserBookings(int userId) async {
    try {
      final response = await _client
          .get(Uri.parse('$baseUrl/bookings/user.php?userId=$userId'))
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          final bookings = (data['bookings'] as List)
              .map((b) => BookingModel.fromJson(b as Map<String, dynamic>))
              .toList();
          return bookings;
        }
      }
      return [];
    } catch (e) {
      throw Exception('Fetch bookings error: $e');
    }
  }

  /// Process payment
  Future<Map<String, dynamic>> processPayment(Map<String, dynamic> payload) async {
    try {
      final response = await _client
          .post(
            Uri.parse('$baseUrl/payments/process.php'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode(payload),
          )
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Payment error: $e');
    }
  }

  /// Register new user
  Future<Map<String, dynamic>> register(Map<String, dynamic> userData) async {
    try {
      final response = await _client
          .post(
            Uri.parse('$baseUrl/auth/register.php'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode(userData),
          )
          .timeout(_timeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Registration error: $e');
    }
  }

  void dispose() {
    _client.close();
  }
}
