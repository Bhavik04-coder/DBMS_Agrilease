class BookingModel {
  final int id;
  final int productId;
  final int renterId;
  final int ownerId;
  final String status; // 'pending', 'confirmed', 'completed', 'cancelled'
  final DateTime startDate;
  final DateTime endDate;
  final double totalPrice;
  final double depositAmount; // 30% of total price
  final bool depositPaid;
  final DateTime? depositPaidDate;
  final double finalAmount; // 70% of total price
  final bool finalPaid;
  final DateTime? finalPaidDate;
  final String? notes;
  final String paymentStatus; // 'pending', 'paid', 'refunded'
  final String? paymentMethod;
  final String? transactionId;
  final DateTime createdAt;

  // Joined display attributes
  final String productTitle;
  final String productImage;
  final double dailyPrice;
  final String renterName;
  final String ownerName;

  const BookingModel({
    required this.id,
    required this.productId,
    required this.renterId,
    required this.ownerId,
    required this.status,
    required this.startDate,
    required this.endDate,
    required this.totalPrice,
    required this.depositAmount,
    this.depositPaid = false,
    this.depositPaidDate,
    required this.finalAmount,
    this.finalPaid = false,
    this.finalPaidDate,
    this.notes,
    this.paymentStatus = 'pending',
    this.paymentMethod,
    this.transactionId,
    required this.createdAt,
    this.productTitle = '',
    this.productImage = '',
    this.dailyPrice = 0.0,
    this.renterName = '',
    this.ownerName = '',
  });

  int get totalDays {
    final diff = endDate.difference(startDate).inDays;
    return diff <= 0 ? 1 : diff + 1;
  }

  bool get isDepositPaid => depositPaid;
  bool get isFullyPaid => depositPaid && finalPaid;

  factory BookingModel.fromJson(Map<String, dynamic> json) {
    final start = json['start_date'] != null
        ? DateTime.tryParse(json['start_date'].toString()) ?? DateTime.now()
        : DateTime.now();
    final end = json['end_date'] != null
        ? DateTime.tryParse(json['end_date'].toString()) ?? DateTime.now()
        : DateTime.now();
    final total = (json['total_price'] != null)
        ? double.tryParse(json['total_price'].toString()) ?? 0.0
        : 0.0;

    return BookingModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      productId: json['product_id'] is int
          ? json['product_id']
          : int.tryParse(json['product_id'].toString()) ?? 0,
      renterId: json['renter_id'] is int
          ? json['renter_id']
          : int.tryParse(json['renter_id'].toString()) ?? 0,
      ownerId: json['owner_id'] is int
          ? json['owner_id']
          : int.tryParse(json['owner_id'].toString()) ?? 0,
      status: json['status'] ?? 'pending',
      startDate: start,
      endDate: end,
      totalPrice: total,
      depositAmount: (json['deposit_amount'] != null)
          ? double.tryParse(json['deposit_amount'].toString()) ?? (total * 0.30)
          : (total * 0.30),
      depositPaid: json['deposit_paid'] == 1 || json['deposit_paid'] == true,
      depositPaidDate: json['deposit_paid_date'] != null
          ? DateTime.tryParse(json['deposit_paid_date'].toString())
          : null,
      finalAmount: (json['final_amount'] != null)
          ? double.tryParse(json['final_amount'].toString()) ?? (total * 0.70)
          : (total * 0.70),
      finalPaid: json['final_paid'] == 1 || json['final_paid'] == true,
      finalPaidDate: json['final_paid_date'] != null
          ? DateTime.tryParse(json['final_paid_date'].toString())
          : null,
      notes: json['notes'],
      paymentStatus: json['payment_status'] ?? 'pending',
      paymentMethod: json['payment_method'],
      transactionId: json['transaction_id'],
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
      productTitle: json['product_title'] ?? '',
      productImage: json['product_image'] ?? '',
      dailyPrice: (json['daily_price'] != null)
          ? double.tryParse(json['daily_price'].toString()) ?? 0.0
          : 0.0,
      renterName: json['renter_name'] ?? '',
      ownerName: json['owner_name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'renter_id': renterId,
      'owner_id': ownerId,
      'status': status,
      'start_date': startDate.toIso8601String().substring(0, 10),
      'end_date': endDate.toIso8601String().substring(0, 10),
      'total_price': totalPrice,
      'deposit_amount': depositAmount,
      'deposit_paid': depositPaid ? 1 : 0,
      'deposit_paid_date': depositPaidDate?.toIso8601String(),
      'final_amount': finalAmount,
      'final_paid': finalPaid ? 1 : 0,
      'final_paid_date': finalPaidDate?.toIso8601String(),
      'notes': notes,
      'payment_status': paymentStatus,
      'payment_method': paymentMethod,
      'transaction_id': transactionId,
      'created_at': createdAt.toIso8601String(),
      'product_title': productTitle,
      'product_image': productImage,
      'daily_price': dailyPrice,
      'renter_name': renterName,
      'owner_name': ownerName,
    };
  }

  BookingModel copyWith({
    int? id,
    int? productId,
    int? renterId,
    int? ownerId,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
    double? totalPrice,
    double? depositAmount,
    bool? depositPaid,
    DateTime? depositPaidDate,
    double? finalAmount,
    bool? finalPaid,
    DateTime? finalPaidDate,
    String? notes,
    String? paymentStatus,
    String? paymentMethod,
    String? transactionId,
    DateTime? createdAt,
    String? productTitle,
    String? productImage,
    double? dailyPrice,
    String? renterName,
    String? ownerName,
  }) {
    return BookingModel(
      id: id ?? this.id,
      productId: productId ?? this.productId,
      renterId: renterId ?? this.renterId,
      ownerId: ownerId ?? this.ownerId,
      status: status ?? this.status,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      totalPrice: totalPrice ?? this.totalPrice,
      depositAmount: depositAmount ?? this.depositAmount,
      depositPaid: depositPaid ?? this.depositPaid,
      depositPaidDate: depositPaidDate ?? this.depositPaidDate,
      finalAmount: finalAmount ?? this.finalAmount,
      finalPaid: finalPaid ?? this.finalPaid,
      finalPaidDate: finalPaidDate ?? this.finalPaidDate,
      notes: notes ?? this.notes,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      transactionId: transactionId ?? this.transactionId,
      createdAt: createdAt ?? this.createdAt,
      productTitle: productTitle ?? this.productTitle,
      productImage: productImage ?? this.productImage,
      dailyPrice: dailyPrice ?? this.dailyPrice,
      renterName: renterName ?? this.renterName,
      ownerName: ownerName ?? this.ownerName,
    );
  }
}
