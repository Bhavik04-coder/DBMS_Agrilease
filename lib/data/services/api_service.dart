import 'dart:convert';
import '../models/user_model.dart';
import '../models/product_model.dart';
import '../models/booking_model.dart';

class ApiService {
  final String baseUrl;

  ApiService({this.baseUrl = 'http://localhost/DBMS_Agrilease'});

  // API endpoints to connect to PHP backend when active
  Future<Map<String, dynamic>> login(String username, String password) async {
    // In production, performs http.post('$baseUrl/api/login.php', ...)
    return {'status': 'success'};
  }

  Future<List<ProductModel>> fetchProducts() async {
    // In production, performs http.get('$baseUrl/api/products.php')
    return [];
  }

  Future<BookingModel?> bookEquipment(Map<String, dynamic> payload) async {
    // In production, performs http.post('$baseUrl/api/book.php', ...)
    return null;
  }
}
