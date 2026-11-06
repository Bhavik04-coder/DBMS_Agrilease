import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/animations/scale_in_animation.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/products_provider.dart';
import '../../../providers/bookings_provider.dart';
import '../../common/product_card.dart';
import '../products/product_detail_screen.dart';
import '../products/add_edit_product_screen.dart';
import '../bookings/my_bookings_screen.dart';
import '../bookings/manage_bookings_screen.dart';

class DashboardScreen extends StatelessWidget {
  final Function(int)? onTabChange;

  const DashboardScreen({super.key, this.onTabChange});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.currentUser;
    final productsProvider = context.watch<ProductsProvider>();
    final bookingsProvider = context.watch<BookingsProvider>();

    if (user == null) {
      return const Center(child: Text('Please log in'));
    }

    final myListings = productsProvider.products.where((p) => p.listedBy == user.id).length;
    final activeBookings = bookingsProvider.myBookings.where((b) => b.status == 'confirmed').length;
    final pendingRequests = bookingsProvider.receivedBookings.where((b) => b.status == 'pending').length;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Icon(Icons.agriculture_rounded, size: 26),
            const SizedBox(width: 8),
            Text(user.fullName),
          ],
        ),
        actions: [
          PopupMenuButton<int>(
            tooltip: 'Switch User Demo',
            icon: const Icon(Icons.swap_horiz_rounded),
            onSelected: (id) => auth.switchUser(id),
            itemBuilder: (_) => [
              const PopupMenuItem(
                value: 1,
                child: Row(
                  children: [
                    Icon(Icons.person, color: AppTheme.primaryGreen),
                    SizedBox(width: 8),
                    Text('Switch to John Farmer'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 2,
                child: Row(
                  children: [
                    Icon(Icons.person, color: AppTheme.accentGold),
                    SizedBox(width: 8),
                    Text('Switch to Mary Agri'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await productsProvider.loadProducts();
          if (user.id > 0) {
            await bookingsProvider.loadUserBookings(user.id);
            await bookingsProvider.loadReceivedBookings(user.id);
          }
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primaryDarkGreen, AppTheme.primaryGreen],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryDarkGreen.withOpacity(0.25),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Welcome back, ${user.fullName.split(' ')[0]}!',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            user.userType.toUpperCase(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      user.address,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.85),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Summary Stats Row
              Row(
                children: [
                  Expanded(
                    child: ScaleInAnimation(
                      duration: const Duration(milliseconds: 400),
                      beginScale: 0.5,
                      child: _buildStatCard(
                        title: 'My Listings',
                        value: myListings.toString(),
                        icon: Icons.list_alt_rounded,
                        color: const Color(0xFF2E7D32),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ScaleInAnimation(
                      duration: const Duration(milliseconds: 500),
                      beginScale: 0.5,
                      child: _buildStatCard(
                        title: 'Active Hires',
                        value: activeBookings.toString(),
                        icon: Icons.calendar_today_rounded,
                        color: const Color(0xFF1976D2),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ScaleInAnimation(
                      duration: const Duration(milliseconds: 600),
                      beginScale: 0.5,
                      child: _buildStatCard(
                        title: 'Requests',
                        value: pendingRequests.toString(),
                        icon: Icons.pending_actions_rounded,
                        color: const Color(0xFFE65100),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Quick Actions Grid
              const Text(
                'Quick Actions',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildActionTile(
                      context,
                      icon: Icons.add_circle_outline_rounded,
                      label: 'List Machinery',
                      color: AppTheme.primaryGreen,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AddEditProductScreen()),
                        );
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildActionTile(
                      context,
                      icon: Icons.search_rounded,
                      label: 'Rent Machinery',
                      color: const Color(0xFF00897B),
                      onTap: () {
                        if (onTabChange != null) {
                          onTabChange!(1); // Go to Explore tab
                        }
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildActionTile(
                      context,
                      icon: Icons.book_online_rounded,
                      label: 'My Bookings',
                      color: const Color(0xFF3949AB),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const MyBookingsScreen()),
                        );
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildActionTile(
                      context,
                      icon: Icons.assignment_turned_in_rounded,
                      label: 'Manage Orders',
                      color: const Color(0xFF6A1B9A),
                      badgeCount: pendingRequests,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const ManageBookingsScreen()),
                        );
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 26),

              // Featured Equipment section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Available in Your Region',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                  TextButton(
                    onPressed: () {
                      if (onTabChange != null) onTabChange!(1);
                    },
                    child: const Text('See All'),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // Horizontal product carousel
              SizedBox(
                height: 240,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: productsProvider.products.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 14),
                  itemBuilder: (context, index) {
                    final product = productsProvider.products[index];
                    return SizedBox(
                      width: 220,
                      child: ProductCard(
                        product: product,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ProductDetailScreen(product: product),
                            ),
                          );
                        },
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

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withOpacity(0.2)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 6,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile(
    BuildContext context, {
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
    int badgeCount = 0,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 14),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withOpacity(0.25)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 13,
                ),
              ),
            ),
            if (badgeCount > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.red,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  badgeCount.toString(),
                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
