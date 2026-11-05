class ReceiptModel {
  final int id;
  final int bookingId;
  final String receiptNumber; // e.g. RCP-00000001
  final double subtotal;
  final double taxAmount; // 18% GST
  final double discountAmount;
  final double totalAmount;
  final double depositAmount;
  final double finalAmount;
  final bool depositPaid;
  final bool finalPaid;
  final String paymentStatus; // 'pending', 'partial', 'paid'
  final DateTime? paymentDate;
  final String? paymentMethod;
  final DateTime generatedAt;

  const ReceiptModel({
    required this.id,
    required this.bookingId,
    required this.receiptNumber,
    required this.subtotal,
    required this.taxAmount,
    this.discountAmount = 0.0,
    required this.totalAmount,
    required this.depositAmount,
    required this.finalAmount,
    required this.depositPaid,
    required this.finalPaid,
    required this.paymentStatus,
    this.paymentDate,
    this.paymentMethod,
    required this.generatedAt,
  });

  factory ReceiptModel.fromBooking({
    required int id,
    required int bookingId,
    required double totalPrice,
    required double depositAmount,
    required double finalAmount,
    required bool depositPaid,
    required bool finalPaid,
    String? paymentMethod,
  }) {
    final tax = totalPrice * 0.18;
    final total = totalPrice * 1.18;
    final status = (depositPaid && finalPaid)
        ? 'paid'
        : (depositPaid ? 'partial' : 'pending');

    return ReceiptModel(
      id: id,
      bookingId: bookingId,
      receiptNumber: 'RCP-${bookingId.toString().padLeft(8, '0')}',
      subtotal: totalPrice,
      taxAmount: tax,
      discountAmount: 0.0,
      totalAmount: total,
      depositAmount: depositAmount,
      finalAmount: finalAmount,
      depositPaid: depositPaid,
      finalPaid: finalPaid,
      paymentStatus: status,
      paymentDate: DateTime.now(),
      paymentMethod: paymentMethod ?? 'UPI',
      generatedAt: DateTime.now(),
    );
  }

  factory ReceiptModel.fromJson(Map<String, dynamic> json) {
    return ReceiptModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      bookingId: json['booking_id'] is int
          ? json['booking_id']
          : int.tryParse(json['booking_id'].toString()) ?? 0,
      receiptNumber: json['receipt_number'] ?? 'RCP-00000000',
      subtotal: (json['subtotal'] != null)
          ? double.tryParse(json['subtotal'].toString()) ?? 0.0
          : 0.0,
      taxAmount: (json['tax_amount'] != null)
          ? double.tryParse(json['tax_amount'].toString()) ?? 0.0
          : 0.0,
      discountAmount: (json['discount_amount'] != null)
          ? double.tryParse(json['discount_amount'].toString()) ?? 0.0
          : 0.0,
      totalAmount: (json['total_amount'] != null)
          ? double.tryParse(json['total_amount'].toString()) ?? 0.0
          : 0.0,
      depositAmount: (json['deposit_amount'] != null)
          ? double.tryParse(json['deposit_amount'].toString()) ?? 0.0
          : 0.0,
      finalAmount: (json['final_amount'] != null)
          ? double.tryParse(json['final_amount'].toString()) ?? 0.0
          : 0.0,
      depositPaid: json['deposit_paid'] == 1 || json['deposit_paid'] == true,
      finalPaid: json['final_paid'] == 1 || json['final_paid'] == true,
      paymentStatus: json['payment_status'] ?? 'pending',
      paymentDate: json['payment_date'] != null
          ? DateTime.tryParse(json['payment_date'].toString())
          : null,
      paymentMethod: json['payment_method'],
      generatedAt: json['generated_at'] != null
          ? DateTime.tryParse(json['generated_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'receipt_number': receiptNumber,
      'subtotal': subtotal,
      'tax_amount': taxAmount,
      'discount_amount': discountAmount,
      'total_amount': totalAmount,
      'deposit_amount': depositAmount,
      'final_amount': finalAmount,
      'deposit_paid': depositPaid ? 1 : 0,
      'final_paid': finalPaid ? 1 : 0,
      'payment_status': paymentStatus,
      'payment_date': paymentDate?.toIso8601String(),
      'payment_method': paymentMethod,
      'generated_at': generatedAt.toIso8601String(),
    };
  }
}
