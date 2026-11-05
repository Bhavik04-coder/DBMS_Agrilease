import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../providers/products_provider.dart';
import '../../common/product_card.dart';
import 'product_detail_screen.dart';

class ProductsScreen extends StatelessWidget {
  const ProductsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final productsProvider = context.watch<ProductsProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Browse Farm Machinery'),
      ),
      body: Column(
        children: [
          // Search & Filter Header
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Column(
              children: [
                TextField(
                  onChanged: (q) => productsProvider.setSearchQuery(q),
                  decoration: InputDecoration(
                    hintText: 'Search equipment (e.g. John Deere, Punjab)...',
                    prefixIcon: const Icon(Icons.search, color: AppTheme.primaryGreen),
                    filled: true,
                    fillColor: const Color(0xFFF1F8E9),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                ),
                const SizedBox(height: 10),
                // Horizontal Category Chips
                SizedBox(
                  height: 40,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: AppConstants.categories.length,
                    separatorBuilder: (_, __) => const SizedBox(width: 8),
                    itemBuilder: (context, index) {
                      final cat = AppConstants.categories[index];
                      final isSelected = productsProvider.selectedCategory == cat['name'];
                      return ChoiceChip(
                        label: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              cat['icon'] as IconData,
                              size: 15,
                              color: isSelected ? Colors.white : AppTheme.primaryDarkGreen,
                            ),
                            const SizedBox(width: 5),
                            Text(cat['name'] as String),
                          ],
                        ),
                        selected: isSelected,
                        selectedColor: AppTheme.primaryGreen,
                        labelStyle: TextStyle(
                          color: isSelected ? Colors.white : AppTheme.textPrimary,
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          fontSize: 12,
                        ),
                        backgroundColor: Colors.white,
                        side: BorderSide(
                          color: isSelected ? AppTheme.primaryGreen : const Color(0xFFCFD8DC),
                        ),
                        onSelected: (_) => productsProvider.setCategory(cat['name'] as String),
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppTheme.dividerColor),

          // Products Grid
          Expanded(
            child: productsProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : productsProvider.products.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.search_off_rounded, size: 64, color: Colors.grey),
                            const SizedBox(height: 12),
                            const Text(
                              'No equipment found',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Try selecting another category or clear search filters',
                              style: TextStyle(color: Colors.grey, fontSize: 13),
                            ),
                            const SizedBox(height: 16),
                            OutlinedButton(
                              onPressed: () {
                                productsProvider.setCategory('All');
                                productsProvider.setSearchQuery('');
                              },
                              child: const Text('Reset Filters'),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => productsProvider.loadProducts(),
                        child: GridView.builder(
                          padding: const EdgeInsets.all(14),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.72,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: productsProvider.products.length,
                          itemBuilder: (context, index) {
                            final product = productsProvider.products[index];
                            return ProductCard(
                              product: product,
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => ProductDetailScreen(product: product),
                                  ),
                                );
                              },
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
