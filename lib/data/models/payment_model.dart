class PaymentModel {
  final int id;
  final int bookingId;
  final double amount;
  final String paymentType; // 'deposit', 'final', 'full', 'refund'
  final String paymentMethod; // 'cash', 'bank_transfer', 'upi', 'card', 'cheque'
  final String? paymentGateway;
  final String transactionId;
  final String paymentStatus; // 'pending', 'completed', 'failed', 'refunded'
  final DateTime paymentDate;
  final bool verifiedByOwner;
  final String? notes;

  const PaymentModel({
    required this.id,
    required this.bookingId,
    required this.amount,
    required this.paymentType,
    required this.paymentMethod,
    this.paymentGateway,
    required this.transactionId,
    this.paymentStatus = 'completed',
    required this.paymentDate,
    this.verifiedByOwner = true,
    this.notes,
  });

  factory PaymentModel.fromJson(Map<String, dynamic> json) {
    return PaymentModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      bookingId: json['booking_id'] is int
          ? json['booking_id']
          : int.tryParse(json['booking_id'].toString()) ?? 0,
      amount: (json['amount'] != null)
          ? double.tryParse(json['amount'].toString()) ?? 0.0
          : 0.0,
      paymentType: json['payment_type'] ?? 'deposit',
      paymentMethod: json['payment_method'] ?? 'upi',
      paymentGateway: json['payment_gateway'],
      transactionId: json['transaction_id'] ?? 'TXN-${DateTime.now().millisecondsSinceEpoch}',
      paymentStatus: json['payment_status'] ?? 'completed',
      paymentDate: json['payment_date'] != null
          ? DateTime.tryParse(json['payment_date'].toString()) ?? DateTime.now()
          : DateTime.now(),
      verifiedByOwner: json['verified_by_owner'] == 1 || json['verified_by_owner'] == true,
      notes: json['notes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'amount': amount,
      'payment_type': paymentType,
      'payment_method': paymentMethod,
      'payment_gateway': paymentGateway,
      'transaction_id': transactionId,
      'payment_status': paymentStatus,
      'payment_date': paymentDate.toIso8601String(),
      'verified_by_owner': verifiedByOwner ? 1 : 0,
      'notes': notes,
    };
  }
}
