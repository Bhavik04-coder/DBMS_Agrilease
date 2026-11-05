import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../data/models/product_model.dart';
import '../../../providers/auth_provider.dart';
import '../../common/custom_button.dart';
import '../bookings/book_product_screen.dart';

class ProductDetailScreen extends StatelessWidget {
  final ProductModel product;

  const ProductDetailScreen({super.key, required this.product});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final currentUser = auth.currentUser;
    final isMyProduct = currentUser != null && currentUser.id == product.listedBy;

    return Scaffold(
      appBar: AppBar(
        title: Text(product.title),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image / Icon Banner
            Container(
              height: 220,
              width: double.infinity,
              color: const Color(0xFFE8F5E9),
              child: Stack(
                children: [
                  Center(
                    child: Icon(
                      _getCategoryIcon(product.category),
                      size: 90,
                      color: AppTheme.primaryGreen.withOpacity(0.8),
                    ),
                  ),
                  Positioned(
                    top: 16,
                    right: 16,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: product.isAvailable
                            ? AppTheme.statusCompleted
                            : AppTheme.statusCancelled,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        product.isAvailable ? 'AVAILABLE NOW' : 'CURRENTLY RENTED',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title & Category
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          product.title,
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryGreen.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          product.category,
                          style: const TextStyle(
                            color: AppTheme.primaryDarkGreen,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Rating & Views
                  Row(
                    children: [
                      const Icon(Icons.star_rounded, color: AppTheme.accentGold, size: 18),
                      const SizedBox(width: 4),
                      Text(
                        '${product.averageRating.toStringAsFixed(1)} (${product.reviewCount} reviews)',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                      const SizedBox(width: 16),
                      const Icon(Icons.visibility_outlined, size: 16, color: AppTheme.textSecondary),
                      const SizedBox(width: 4),
                      Text(
                        '${product.views} views',
                        style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Location
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF5F5F5),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.location_on, color: AppTheme.primaryGreen, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            product.location,
                            style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13),
                          ),
                        ),
                        Text(
                          'Lat: ${product.lat.toStringAsFixed(2)}, Lng: ${product.lng.toStringAsFixed(2)}',
                          style: const TextStyle(fontSize: 10, color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Pricing & Split Payment Highlight
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F5E9),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFC8E6C9)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Daily Rental Rate',
                              style: TextStyle(fontSize: 14, color: AppTheme.textSecondary),
                            ),
                            Text(
                              '${Formatters.formatCurrency(product.price)} / day',
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primaryDarkGreen,
                              ),
                            ),
                          ],
                        ),
                        const Divider(height: 20),
                        const Row(
                          children: [
                            Icon(Icons.shield_outlined, size: 18, color: AppTheme.primaryGreen),
                            SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                'Split Payment Guarantee: Pay only 30% deposit to reserve. Balance 70% paid after equipment handover.',
                                style: TextStyle(fontSize: 12, color: AppTheme.primaryDarkGreen),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 22),

                  // Description
                  const Text(
                    'Equipment Overview & Specs',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    product.description,
                    style: const TextStyle(
                      fontSize: 14,
                      color: Color(0xFF424242),
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 22),

                  // Owner Information
                  const Text(
                    'Equipment Owner',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 10),
                  ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    tileColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                      side: const BorderSide(color: Color(0xFFE0E0E0)),
                    ),
                    leading: CircleAvatar(
                      backgroundColor: AppTheme.primaryGreen,
                      child: Text(
                        product.ownerName != null && product.ownerName!.isNotEmpty
                            ? product.ownerName![0]
                            : 'O',
                        style: const TextStyle(color: Colors.white),
                      ),
                    ),
                    title: Text(
                      product.ownerName ?? 'Verified Farm Owner',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Text(
                      product.ownerPhone ?? '+91 98765 00000',
                      style: const TextStyle(fontSize: 12),
                    ),
                    trailing: const Icon(Icons.verified, color: AppTheme.primaryGreen, size: 20),
                  ),
                  const SizedBox(height: 90), // Bottom bar clearance
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: SafeArea(
          child: Row(
            children: [
              Expanded(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Deposit (30%)', style: TextStyle(fontSize: 11, color: Colors.grey)),
                    Text(
                      Formatters.formatCurrency(product.price * 0.30),
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDarkGreen,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                flex: 2,
                child: CustomButton(
                  text: isMyProduct ? 'Your Listing' : 'Rent This Equipment',
                  icon: isMyProduct ? Icons.check : Icons.calendar_month_rounded,
                  onPressed: isMyProduct || !product.isAvailable
                      ? null
                      : () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => BookProductScreen(product: product),
                            ),
                          );
                        },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getCategoryIcon(String category) {
    switch (category.toLowerCase()) {
      case 'tractors':
        return Icons.agriculture_rounded;
      case 'harvesters':
        return Icons.grass_rounded;
      case 'tillers':
        return Icons.terrain_rounded;
      case 'seeders':
        return Icons.grain_rounded;
      case 'sprayers':
        return Icons.water_drop_rounded;
      default:
        return Icons.agriculture_rounded;
    }
  }
}
