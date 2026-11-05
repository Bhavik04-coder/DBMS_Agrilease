class ProductModel {
  final int id;
  final String title;
  final String description;
  final String category;
  final double price; // per day rate in INR
  final String imagePath;
  final String location;
  final double lat;
  final double lng;
  final int listedBy;
  final String status; // 'available', 'booked', 'maintenance'
  final int views;
  final double averageRating;
  final int reviewCount;
  final String? ownerName;
  final String? ownerPhone;

  const ProductModel({
    required this.id,
    required this.title,
    required this.description,
    required this.category,
    required this.price,
    required this.imagePath,
    required this.location,
    required this.lat,
    required this.lng,
    required this.listedBy,
    this.status = 'available',
    this.views = 0,
    this.averageRating = 5.0,
    this.reviewCount = 0,
    this.ownerName,
    this.ownerPhone,
  });

  bool get isAvailable => status.toLowerCase() == 'available';

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    return ProductModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      category: json['category'] ?? 'General',
      price: (json['price'] != null) ? double.tryParse(json['price'].toString()) ?? 0.0 : 0.0,
      imagePath: json['image_path'] ?? 'assets/images/default_tractor.png',
      location: json['location'] ?? '',
      lat: (json['lat'] != null) ? double.tryParse(json['lat'].toString()) ?? 0.0 : 0.0,
      lng: (json['lng'] != null) ? double.tryParse(json['lng'].toString()) ?? 0.0 : 0.0,
      listedBy: json['listed_by'] is int ? json['listed_by'] : int.tryParse(json['listed_by'].toString()) ?? 1,
      status: json['status'] ?? 'available',
      views: json['views'] is int ? json['views'] : int.tryParse(json['views']?.toString() ?? '0') ?? 0,
      averageRating: (json['average_rating'] != null)
          ? double.tryParse(json['average_rating'].toString()) ?? 5.0
          : 5.0,
      reviewCount: json['review_count'] is int
          ? json['review_count']
          : int.tryParse(json['review_count']?.toString() ?? '0') ?? 0,
      ownerName: json['owner_name'],
      ownerPhone: json['owner_phone'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'category': category,
      'price': price,
      'image_path': imagePath,
      'location': location,
      'lat': lat,
      'lng': lng,
      'listed_by': listedBy,
      'status': status,
      'views': views,
      'average_rating': averageRating,
      'review_count': reviewCount,
      'owner_name': ownerName,
      'owner_phone': ownerPhone,
    };
  }

  ProductModel copyWith({
    int? id,
    String? title,
    String? description,
    String? category,
    double? price,
    String? imagePath,
    String? location,
    double? lat,
    double? lng,
    int? listedBy,
    String? status,
    int? views,
    double? averageRating,
    int? reviewCount,
    String? ownerName,
    String? ownerPhone,
  }) {
    return ProductModel(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      category: category ?? this.category,
      price: price ?? this.price,
      imagePath: imagePath ?? this.imagePath,
      location: location ?? this.location,
      lat: lat ?? this.lat,
      lng: lng ?? this.lng,
      listedBy: listedBy ?? this.listedBy,
      status: status ?? this.status,
      views: views ?? this.views,
      averageRating: averageRating ?? this.averageRating,
      reviewCount: reviewCount ?? this.reviewCount,
      ownerName: ownerName ?? this.ownerName,
      ownerPhone: ownerPhone ?? this.ownerPhone,
    );
  }
}
