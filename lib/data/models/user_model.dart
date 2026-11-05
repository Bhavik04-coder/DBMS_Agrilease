class UserModel {
  final int id;
  final String username;
  final String fullName;
  final String email;
  final String phone;
  final String address;
  final double lat;
  final double lng;
  final String userType; // 'renter', 'owner', 'both'
  final String? profileImage;
  final bool isActive;

  const UserModel({
    required this.id,
    required this.username,
    required this.fullName,
    required this.email,
    required this.phone,
    required this.address,
    required this.lat,
    required this.lng,
    this.userType = 'both',
    this.profileImage,
    this.isActive = true,
  });

  bool get isOwner => userType == 'owner' || userType == 'both';
  bool get isRenter => userType == 'renter' || userType == 'both';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      username: json['username'] ?? '',
      fullName: json['full_name'] ?? json['username'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'] ?? '',
      address: json['address'] ?? '',
      lat: (json['lat'] != null) ? double.tryParse(json['lat'].toString()) ?? 0.0 : 0.0,
      lng: (json['lng'] != null) ? double.tryParse(json['lng'].toString()) ?? 0.0 : 0.0,
      userType: json['user_type'] ?? 'both',
      profileImage: json['profile_image'],
      isActive: json['is_active'] == 1 || json['is_active'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'full_name': fullName,
      'email': email,
      'phone': phone,
      'address': address,
      'lat': lat,
      'lng': lng,
      'user_type': userType,
      'profile_image': profileImage,
      'is_active': isActive ? 1 : 0,
    };
  }

  UserModel copyWith({
    int? id,
    String? username,
    String? fullName,
    String? email,
    String? phone,
    String? address,
    double? lat,
    double? lng,
    String? userType,
    String? profileImage,
    bool? isActive,
  }) {
    return UserModel(
      id: id ?? this.id,
      username: username ?? this.username,
      fullName: fullName ?? this.fullName,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      address: address ?? this.address,
      lat: lat ?? this.lat,
      lng: lng ?? this.lng,
      userType: userType ?? this.userType,
      profileImage: profileImage ?? this.profileImage,
      isActive: isActive ?? this.isActive,
    );
  }
}
