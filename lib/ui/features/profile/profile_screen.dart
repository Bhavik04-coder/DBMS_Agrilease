import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import '../products/my_products_screen.dart';
import '../bookings/my_bookings_screen.dart';
import '../bookings/manage_bookings_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.currentUser;

    if (user == null) {
      return const Scaffold(body: Center(child: Text('Please log in')));
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Farmer Profile & Account'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // User Avatar & Name Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE0E0E0)),
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 38,
                    backgroundColor: AppTheme.primaryGreen,
                    child: Text(
                      user.fullName.isNotEmpty ? user.fullName[0] : 'U',
                      style: const TextStyle(fontSize: 30, color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user.fullName,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    '@${user.username}',
                    style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryGreen.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Account: ${user.userType.toUpperCase()}',
                      style: const TextStyle(
                        color: AppTheme.primaryDarkGreen,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Profile Info List
            Card(
              child: Column(
                children: [
                  _buildProfileTile(Icons.phone_outlined, 'Phone', user.phone),
                  const Divider(height: 1),
                  _buildProfileTile(Icons.email_outlined, 'Email', user.email),
                  const Divider(height: 1),
                  _buildProfileTile(Icons.location_on_outlined, 'Address', user.address),
                  const Divider(height: 1),
                  _buildProfileTile(
                    Icons.gps_fixed_rounded,
                    'Coordinates',
                    'Lat: ${user.lat.toStringAsFixed(4)}, Lng: ${user.lng.toStringAsFixed(4)}',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Management Navigation
            Card(
              child: Column(
                children: [
                  ListTile(
                    leading: const Icon(Icons.inventory_2_outlined, color: AppTheme.primaryGreen),
                    title: const Text('My Listed Machinery'),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const MyProductsScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.book_online_outlined, color: Color(0xFF1976D2)),
                    title: const Text('My Hire Bookings'),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const MyBookingsScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.assignment_turned_in_outlined, color: Color(0xFF6A1B9A)),
                    title: const Text('Incoming Hire Requests'),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ManageBookingsScreen()),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Demo Switch & Logout
            Card(
              child: ListTile(
                leading: const Icon(Icons.swap_horiz_rounded, color: AppTheme.accentGold),
                title: const Text('Switch Demo Profile'),
                subtitle: Text(user.id == 1 ? 'Switch to Mary Agriculture' : 'Switch to John Farmer'),
                onTap: () {
                  auth.switchUser(user.id == 1 ? 2 : 1);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Switched to ${auth.currentUser?.fullName}')),
                  );
                },
              ),
            ),
            const SizedBox(height: 14),

            Card(
              child: ListTile(
                leading: const Icon(Icons.logout_rounded, color: Colors.red),
                title: const Text('Sign Out of AgriLease', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                onTap: () {
                  auth.logout();
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileTile(IconData icon, String label, String value) {
    return ListTile(
      leading: Icon(icon, color: AppTheme.primaryDarkGreen, size: 22),
      title: Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
      subtitle: Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
    );
  }
}
