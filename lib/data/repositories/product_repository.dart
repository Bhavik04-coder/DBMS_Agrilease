import '../models/product_model.dart';
import '../services/api_service.dart';
import '../services/mock_database.dart';
import '../../core/config/app_config.dart';

class ProductRepository {
  final ApiService _apiService;
  final MockDatabase _mockDb = MockDatabase();

  ProductRepository(this._apiService);

  Future<List<ProductModel>> getProducts({
    String? category,
    String? searchQuery,
    double? maxPrice,
  }) async {
    try {
      // Use mock data if enabled
      if (AppConfig.enableMockData) {
        await Future.delayed(const Duration(milliseconds: 150));
        var list = _mockDb.products.toList();

        if (category != null && category.isNotEmpty && category != 'All') {
          list = list.where((p) => p.category.toLowerCase() == category.toLowerCase()).toList();
        }

        if (searchQuery != null && searchQuery.trim().isNotEmpty) {
          final q = searchQuery.trim().toLowerCase();
          list = list.where((p) =>
            p.title.toLowerCase().contains(q) ||
            p.description.toLowerCase().contains(q) ||
            p.location.toLowerCase().contains(q)
          ).toList();
        }

        if (maxPrice != null && maxPrice > 0) {
          list = list.where((p) => p.price <= maxPrice).toList();
        }

        return list;
      }
      
      // Use real API
      var products = await _apiService.fetchProducts(
        category: category,
        search: searchQuery,
      );

      if (maxPrice != null && maxPrice > 0) {
        products = products.where((p) => p.price <= maxPrice).toList();
      }

      return products;
    } catch (e) {
      if (AppConfig.enableLogging) print('Error fetching products: $e');
      rethrow;
    }
  }

  Future<ProductModel?> getProductById(int id) async {
    try {
      if (AppConfig.enableMockData) {
        await Future.delayed(const Duration(milliseconds: 100));
        try {
          final product = _mockDb.products.firstWhere((p) => p.id == id);
          return product;
        } catch (_) {
          return null;
        }
      }
      
      return await _apiService.fetchProductById(id);
    } catch (e) {
      if (AppConfig.enableLogging) print('Error fetching product: $e');
      return null;
    }
  }

  Future<List<ProductModel>> getMyProducts(int userId) async {
    try {
      if (AppConfig.enableMockData) {
        await Future.delayed(const Duration(milliseconds: 150));
        return _mockDb.products.where((p) => p.listedBy == userId).toList();
      }
      
      // TODO: Implement API call for user's products
      return [];
    } catch (e) {
      if (AppConfig.enableLogging) print('Error fetching my products: $e');
      return [];
    }
  }

  Future<ProductModel> addProduct({
    required String title,
    required String description,
    required String category,
    required double price,
    required String location,
    required int listedBy,
    String? imagePath,
    double lat = 30.7333,
    double lng = 76.7794,
    String? ownerName,
    String? ownerPhone,
  }) async {
    try {
      if (AppConfig.enableMockData) {
        await Future.delayed(const Duration(milliseconds: 250));
        final newProduct = ProductModel(
          id: _mockDb.products.length + 1,
          title: title,
          description: description,
          category: category,
          price: price,
          imagePath: imagePath ?? 'assets/images/default_tractor.png',
          location: location,
          lat: lat,
          lng: lng,
          listedBy: listedBy,
          status: 'available',
          views: 0,
          averageRating: 5.0,
          reviewCount: 0,
          ownerName: ownerName,
          ownerPhone: ownerPhone,
        );

        _mockDb.products.insert(0, newProduct);
        return newProduct;
      }
      
      // TODO: Implement API call for adding product
      throw Exception('Not implemented');
    } catch (e) {
      if (AppConfig.enableLogging) print('Error adding product: $e');
      rethrow;
    }
  }

  Future<void> updateProduct(ProductModel product) async {
    try {
      if (AppConfig.enableMockData) {
        await Future.delayed(const Duration(milliseconds: 200));
        final index = _mockDb.products.indexWhere((p) => p.id == product.id);
        if (index != -1) {
          _mockDb.products[index] = product;
        }
        return;
      }
      
      // TODO: Implement API call for updating product
    } catch (e) {
      if (AppConfig.enableLogging) print('Error updating product: $e');
      rethrow;
    }
  }
}
