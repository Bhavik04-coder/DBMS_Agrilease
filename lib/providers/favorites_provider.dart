import 'package:flutter/material.dart';
import 'package:get_storage/get_storage.dart';

class FavoritesProvider with ChangeNotifier {
  final _box = GetStorage();
  static const String _favKey = 'favorites';
  
  late Set<int> _favorites;

  FavoritesProvider() {
    _loadFavorites();
  }

  Set<int> get favorites => _favorites;
  
  int get favoritesCount => _favorites.length;

  void _loadFavorites() {
    final saved = _box.read<List>(_favKey);
    _favorites = (saved ?? []).cast<int>().toSet();
  }

  bool isFavorite(int productId) => _favorites.contains(productId);

  Future<void> toggleFavorite(int productId) async {
    if (_favorites.contains(productId)) {
      _favorites.remove(productId);
    } else {
      _favorites.add(productId);
    }
    await _box.write(_favKey, _favorites.toList());
    notifyListeners();
  }

  Future<void> addFavorite(int productId) async {
    _favorites.add(productId);
    await _box.write(_favKey, _favorites.toList());
    notifyListeners();
  }

  Future<void> removeFavorite(int productId) async {
    _favorites.remove(productId);
    await _box.write(_favKey, _favorites.toList());
    notifyListeners();
  }

  Future<void> clearFavorites() async {
    _favorites.clear();
    await _box.write(_favKey, []);
    notifyListeners();
  }
}
