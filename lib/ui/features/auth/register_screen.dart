import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../providers/auth_provider.dart';
import '../../common/custom_button.dart';
import '../../common/custom_text_field.dart';
import '../navigation/main_navigation_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _fullNameController = TextEditingController();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  String _selectedRole = 'both';

  @override
  void dispose() {
    _fullNameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = context.read<AuthProvider>();
    final success = await auth.register(
      username: _usernameController.text.trim(),
      fullName: _fullNameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      address: _addressController.text.trim(),
      userType: _selectedRole,
    );

    if (success && mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Create AgriLease Account'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Join the Agricultural Sharing Network',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDarkGreen,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Rent machinery or monetize your farm equipment',
                style: TextStyle(fontSize: 13, color: AppTheme.textSecondary),
              ),
              const SizedBox(height: 24),

              CustomTextField(
                controller: _fullNameController,
                label: 'Full Name',
                hint: 'e.g. Ramesh Singh',
                prefixIcon: Icons.badge_outlined,
                validator: (v) => v == null || v.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _usernameController,
                label: 'Username',
                hint: 'e.g. ramesh_farmer',
                prefixIcon: Icons.person_outline,
                validator: (v) => v == null || v.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _emailController,
                label: 'Email Address',
                hint: 'ramesh@example.com',
                prefixIcon: Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
                validator: (v) => v == null || !v.contains('@') ? 'Enter a valid email' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _phoneController,
                label: 'Phone Number',
                hint: '+91 98765 00000',
                prefixIcon: Icons.phone_outlined,
                keyboardType: TextInputType.phone,
                validator: (v) => v == null || v.length < 10 ? 'Enter valid phone' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _addressController,
                label: 'Farm / Village Address',
                hint: 'Village Road, District, State',
                prefixIcon: Icons.home_work_outlined,
                validator: (v) => v == null || v.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _passwordController,
                label: 'Password',
                hint: '••••••••',
                prefixIcon: Icons.lock_outline,
                obscureText: true,
                validator: (v) => v == null || v.length < 6 ? 'Min 6 characters' : null,
              ),
              const SizedBox(height: 20),

              // Account Role Selection
              const Text(
                'Account Role',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF37474F)),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  _buildRoleRadio('both', 'Both (Rent & List)'),
                  const SizedBox(width: 8),
                  _buildRoleRadio('renter', 'Renter Only'),
                  const SizedBox(width: 8),
                  _buildRoleRadio('owner', 'Owner Only'),
                ],
              ),
              const SizedBox(height: 24),

              if (auth.errorMessage != null) ...[
                Text(
                  auth.errorMessage!,
                  style: const TextStyle(color: Colors.red, fontSize: 13),
                ),
                const SizedBox(height: 12),
              ],

              SizedBox(
                width: double.infinity,
                child: CustomButton(
                  text: 'Register Account',
                  onPressed: _submit,
                  isLoading: auth.isLoading,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRoleRadio(String value, String label) {
    final isSelected = _selectedRole == value;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _selectedRole = value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppTheme.primaryGreen.withOpacity(0.1) : Colors.white,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: isSelected ? AppTheme.primaryGreen : const Color(0xFFCFD8DC),
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Center(
            child: Text(
              label,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                color: isSelected ? AppTheme.primaryDarkGreen : AppTheme.textPrimary,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
