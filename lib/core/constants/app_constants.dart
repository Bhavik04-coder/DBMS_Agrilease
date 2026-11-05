import 'package:flutter/material.dart';

class AppConstants {
  static const String appName = 'AgriLease';
  static const String appTagline = 'Smart Agricultural Equipment Rental';
  static const String baseApiUrl = 'http://localhost/DBMS_Agrilease';

  // Equipment categories
  static const List<Map<String, dynamic>> categories = [
    {'name': 'All', 'icon': Icons.grid_view_rounded},
    {'name': 'Tractors', 'icon': Icons.agriculture_rounded},
    {'name': 'Harvesters', 'icon': Icons.grass_rounded},
    {'name': 'Tillers', 'icon': Icons.terrain_rounded},
    {'name': 'Seeders', 'icon': Icons.grain_rounded},
    {'name': 'Sprayers', 'icon': Icons.water_drop_rounded},
    {'name': 'Irrigation', 'icon': Icons.water_rounded},
    {'name': 'Trailers', 'icon': Icons.local_shipping_rounded},
  ];

  // Payment methods
  static const List<Map<String, dynamic>> paymentMethods = [
    {
      'id': 'upi',
      'title': 'UPI (GPay / PhonePe / Paytm)',
      'icon': Icons.qr_code_2_rounded,
      'description': 'Instant & secure UPI payment'
    },
    {
      'id': 'card',
      'title': 'Debit / Credit Card',
      'icon': Icons.credit_card_rounded,
      'description': 'Visa, Mastercard, RuPay'
    },
    {
      'id': 'bank_transfer',
      'title': 'Net Banking / NEFT',
      'icon': Icons.account_balance_rounded,
      'description': 'Direct bank account transfer'
    },
    {
      'id': 'cash',
      'title': 'Cash on Delivery',
      'icon': Icons.payments_outlined,
      'description': 'Pay in cash directly to equipment owner'
    },
  ];

  // Booking statuses
  static const String statusPending = 'pending';
  static const String statusConfirmed = 'confirmed';
  static const String statusCompleted = 'completed';
  static const String statusCancelled = 'cancelled';
}
