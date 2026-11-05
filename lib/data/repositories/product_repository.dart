import '../models/product_model.dart';
import '../services/mock_database.dart';

class ProductRepository {
  final MockDatabase _db = MockDatabase();

  Future<List<ProductModel>> getProducts({
    String? category,
    String? searchQuery,
    double? maxPrice,
  }) async {
    await Future.delayed(const Duration(milliseconds: 150));
    var list = _db.products.toList();

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

  Future<ProductModel?> getProductById(int id) async {
    await Future.delayed(const Duration(milliseconds: 100));
    try {
      final product = _db.products.firstWhere((p) => p.id == id);
      return product;
    } catch (_) {
      return null;
    }
  }

  Future<List<ProductModel>> getMyProducts(int userId) async {
    await Future.delayed(const Duration(milliseconds: 150));
    return _db.products.where((p) => p.listedBy == userId).toList();
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
    await Future.delayed(const Duration(milliseconds: 250));
    final newProduct = ProductModel(
      id: _db.products.length + 1,
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

    _db.products.insert(0, newProduct);
    return newProduct;
  }

  Future<void> updateProduct(ProductModel product) async {
    await Future.delayed(const Duration(milliseconds: 200));
    final index = _db.products.indexWhere((p) => p.id == product.id);
    if (index != -1) {
      _db.products[index] = product;
    }
  }
}
