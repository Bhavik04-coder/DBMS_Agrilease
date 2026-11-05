import 'package:flutter/material.dart';
import '../data/models/product_model.dart';
import '../data/repositories/product_repository.dart';

class ProductsProvider extends ChangeNotifier {
  final ProductRepository _repo = ProductRepository();

  List<ProductModel> _products = [];
  List<ProductModel> _myProducts = [];
  String _selectedCategory = 'All';
  String _searchQuery = '';
  double? _maxPrice;
  bool _isLoading = false;
  String? _errorMessage;

  List<ProductModel> get products => _products;
  List<ProductModel> get myProducts => _myProducts;
  String get selectedCategory => _selectedCategory;
  String get searchQuery => _searchQuery;
  double? get maxPrice => _maxPrice;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  ProductsProvider() {
    loadProducts();
  }

  Future<void> loadProducts() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _products = await _repo.getProducts(
        category: _selectedCategory,
        searchQuery: _searchQuery,
        maxPrice: _maxPrice,
      );
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  Future<void> loadMyProducts(int userId) async {
    _isLoading = true;
    notifyListeners();

    try {
      _myProducts = await _repo.getMyProducts(userId);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  void setCategory(String category) {
    if (_selectedCategory == category) return;
    _selectedCategory = category;
    loadProducts();
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    loadProducts();
  }

  void setMaxPrice(double? max) {
    _maxPrice = max;
    loadProducts();
  }

  Future<bool> addProduct({
    required String title,
    required String description,
    required String category,
    required double price,
    required String location,
    required int listedBy,
    String? ownerName,
    String? ownerPhone,
  }) async {
    try {
      await _repo.addProduct(
        title: title,
        description: description,
        category: category,
        price: price,
        location: location,
        listedBy: listedBy,
        ownerName: ownerName,
        ownerPhone: ownerPhone,
      );
      await loadProducts();
      await loadMyProducts(listedBy);
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }
}
