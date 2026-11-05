import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/products_provider.dart';
import '../../common/custom_button.dart';
import '../../common/custom_text_field.dart';

class AddEditProductScreen extends StatefulWidget {
  const AddEditProductScreen({super.key});

  @override
  State<AddEditProductScreen> createState() => _AddEditProductScreenState();
}

class _AddEditProductScreenState extends State<AddEditProductScreen> {
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _priceController = TextEditingController();
  final _locationController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  String _selectedCategory = 'Tractors';
  bool _isSubmitting = false;

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _priceController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = context.read<AuthProvider>();
    final user = auth.currentUser;
    if (user == null) return;

    setState(() => _isSubmitting = true);

    final productsProvider = context.read<ProductsProvider>();
    final success = await productsProvider.addProduct(
      title: _titleController.text.trim(),
      description: _descController.text.trim(),
      category: _selectedCategory,
      price: double.tryParse(_priceController.text.trim()) ?? 1000.0,
      location: _locationController.text.trim(),
      listedBy: user.id,
      ownerName: user.fullName,
      ownerPhone: user.phone,
    );

    setState(() => _isSubmitting = false);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Equipment listed successfully!'),
          backgroundColor: AppTheme.primaryDarkGreen,
        ),
      );
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    final categories = AppConstants.categories.where((c) => c['name'] != 'All').toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('List New Machinery'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Equipment Details',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.primaryDarkGreen),
              ),
              const SizedBox(height: 6),
              const Text(
                'Fill in the machinery specifications to list for rent across the farmer community.',
                style: TextStyle(fontSize: 13, color: AppTheme.textSecondary),
              ),
              const SizedBox(height: 20),

              CustomTextField(
                controller: _titleController,
                label: 'Equipment Title / Model Name',
                hint: 'e.g. Swaraj 744 FE Tractor',
                prefixIcon: Icons.agriculture_rounded,
                validator: (v) => v == null || v.isEmpty ? 'Please enter model name' : null,
              ),
              const SizedBox(height: 16),

              // Category Selector
              const Text(
                'Category',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF37474F)),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFCFD8DC)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _selectedCategory,
                    isExpanded: true,
                    items: categories.map((cat) {
                      return DropdownMenuItem<String>(
                        value: cat['name'] as String,
                        child: Row(
                          children: [
                            Icon(cat['icon'] as IconData, size: 18, color: AppTheme.primaryGreen),
                            const SizedBox(width: 10),
                            Text(cat['name'] as String),
                          ],
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) setState(() => _selectedCategory = val);
                    },
                  ),
                ),
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _priceController,
                label: 'Rental Rate per Day (₹ INR)',
                hint: 'e.g. 1800',
                prefixIcon: Icons.currency_rupee_rounded,
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Enter daily rate';
                  if (double.tryParse(v) == null) return 'Enter a valid amount';
                  return null;
                },
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _locationController,
                label: 'Location / Farm Address',
                hint: 'e.g. Moga Road, Ludhiana, Punjab',
                prefixIcon: Icons.location_on_outlined,
                suffixIcon: IconButton(
                  icon: const Icon(Icons.my_location_rounded, color: AppTheme.primaryGreen),
                  tooltip: 'Auto-detect GPS',
                  onPressed: () {
                    _locationController.text = 'Punjab Farm Zone, Lat: 30.73, Lng: 76.77';
                  },
                ),
                validator: (v) => v == null || v.isEmpty ? 'Enter location' : null,
              ),
              const SizedBox(height: 16),

              CustomTextField(
                controller: _descController,
                label: 'Full Description & Technical Specs',
                hint: 'Describe horsepower, condition, fuel capacity, attachments included...',
                prefixIcon: Icons.description_outlined,
                maxLines: 4,
                validator: (v) => v == null || v.length < 10 ? 'Enter detailed description' : null,
              ),
              const SizedBox(height: 28),

              SizedBox(
                width: double.infinity,
                child: CustomButton(
                  text: 'Publish Machinery Listing',
                  icon: Icons.check_circle_outline_rounded,
                  isLoading: _isSubmitting,
                  onPressed: _submit,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
