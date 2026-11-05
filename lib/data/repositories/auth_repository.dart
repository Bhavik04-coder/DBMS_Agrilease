import '../models/user_model.dart';
import '../services/mock_database.dart';

class AuthRepository {
  final MockDatabase _db = MockDatabase();

  UserModel? get currentUser => _db.currentUser;

  Future<UserModel> login(String username, String password) async {
    await Future.delayed(const Duration(milliseconds: 300));
    final user = _db.users.firstWhere(
      (u) => u.username.toLowerCase() == username.trim().toLowerCase(),
      orElse: () => throw Exception('User not found. Try john_farmer or mary_agri'),
    );
    _db.currentUser = user;
    return user;
  }

  Future<UserModel> register({
    required String username,
    required String fullName,
    required String email,
    required String phone,
    required String address,
    required String userType,
    double lat = 30.7333,
    double lng = 76.7794,
  }) async {
    await Future.delayed(const Duration(milliseconds: 300));
    final existing = _db.users.where((u) => u.username == username || u.email == email);
    if (existing.isNotEmpty) {
      throw Exception('Username or email already exists');
    }

    final newUser = UserModel(
      id: _db.users.length + 1,
      username: username,
      fullName: fullName,
      email: email,
      phone: phone,
      address: address,
      lat: lat,
      lng: lng,
      userType: userType,
    );

    _db.users.add(newUser);
    _db.currentUser = newUser;
    return newUser;
  }

  void switchUser(int userId) {
    final user = _db.users.firstWhere((u) => u.id == userId);
    _db.currentUser = user;
  }

  void logout() {
    _db.currentUser = null;
  }
}
