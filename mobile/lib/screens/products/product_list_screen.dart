import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../core/responsive/responsive_layout.dart';
import '../../models/product_model.dart';
import '../../providers/catalog_provider.dart';
import '../../widgets/product_card.dart';
import '../../widgets/search_app_bar.dart';

class ProductListScreen extends ConsumerStatefulWidget {
  final int? categoryId;
  final String? categoryTitle;
  final String? searchQuery;

  const ProductListScreen({
    super.key,
    this.categoryId,
    this.categoryTitle,
    this.searchQuery,
  });

  @override
  ConsumerState<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends ConsumerState<ProductListScreen> {
  List<ProductModel> _products = [];
  bool _isLoading = true;
  int _totalCount = 0;
  String _selectedSort = 'popularity';
  int? _selectedBrandId;
  double? _minPrice;
  double? _maxPrice;

  @override
  void initState() {
    super.initState();
    _fetchProducts();
  }

  Future<void> _fetchProducts() async {
    setState(() => _isLoading = true);
    final repo = ref.read(catalogRepositoryProvider);

    try {
      if (widget.searchQuery != null && widget.searchQuery!.isNotEmpty) {
        final res = await repo.search(
          widget.searchQuery!,
          sort: _selectedSort,
          categoryId: widget.categoryId,
          brandId: _selectedBrandId,
        );
        setState(() {
          _products = res['products'];
          _totalCount = res['pagination']?['total_items'] ?? _products.length;
          _isLoading = false;
        });
      } else {
        final res = await repo.getProducts(
          categoryId: widget.categoryId,
          brandId: _selectedBrandId,
          minPrice: _minPrice,
          maxPrice: _maxPrice,
          sort: _selectedSort,
        );
        setState(() {
          _products = res['products'];
          _totalCount = res['pagination']?['total_items'] ?? _products.length;
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDesktop = ResponsiveLayout.isDesktop(context);
    final title = widget.categoryTitle ?? (widget.searchQuery != null ? 'Results for "${widget.searchQuery}"' : 'All Products');

    return Scaffold(
      appBar: SearchAppBar(
        showBackButton: true,
        initialQuery: widget.searchQuery,
      ),
      body: Column(
        children: [
          // Filter & Sort Control Bar
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        '$_totalCount Products Available',
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                // Sort Button
                InkWell(
                  onTap: _showSortBottomSheet,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      border: Border.all(color: AppColors.border),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Row(
                      children: const [
                        Icon(Icons.swap_vert, size: 16, color: AppColors.textPrimary),
                        SizedBox(width: 4),
                        Text('Sort', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // Filter Button
                InkWell(
                  onTap: _showFilterBottomSheet,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      border: Border.all(color: AppColors.border),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Row(
                      children: const [
                        Icon(Icons.filter_list, size: 16, color: AppColors.textPrimary),
                        SizedBox(width: 4),
                        Text('Filter', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.divider),

          // Main Product Grid
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _products.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Icon(Icons.search_off, size: 64, color: Colors.grey),
                            SizedBox(height: 12),
                            Text('No matching products found', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            SizedBox(height: 6),
                            Text('Try searching with different filters or keywords', style: TextStyle(color: Colors.grey)),
                          ],
                        ),
                      )
                    : GridView.builder(
                        padding: EdgeInsets.all(isDesktop ? 24 : 8),
                        itemCount: _products.length,
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: isDesktop ? 4 : 2,
                          childAspectRatio: 0.65,
                          crossAxisSpacing: 8,
                          mainAxisSpacing: 8,
                        ),
                        itemBuilder: (context, index) {
                          final p = _products[index];
                          return ProductCard(
                            product: p,
                            onTap: () => context.push('/products/${p.id}'),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  void _showSortBottomSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                child: Text('SORT BY', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
              ),
              _buildSortOption('Relevance / Popularity', 'popularity'),
              _buildSortOption('Price -- Low to High', 'price_low'),
              _buildSortOption('Price -- High to Low', 'price_high'),
              _buildSortOption('Customer Rating', 'rating'),
              _buildSortOption('Newest First', 'newest'),
              _buildSortOption('Discount Percentage', 'discount'),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSortOption(String label, String value) {
    final isSelected = _selectedSort == value;
    return ListTile(
      title: Text(
        label,
        style: TextStyle(
          fontSize: 14,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? AppColors.primary : AppColors.textPrimary,
        ),
      ),
      trailing: isSelected ? const Icon(Icons.check, color: AppColors.primary) : null,
      onTap: () {
        setState(() => _selectedSort = value);
        Navigator.pop(context);
        _fetchProducts();
      },
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            return SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Filters', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _selectedBrandId = null;
                              _minPrice = null;
                              _maxPrice = null;
                            });
                            Navigator.pop(context);
                            _fetchProducts();
                          },
                          child: const Text('Clear All'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Text('Price Range', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        _buildPriceChip('Under ₹1,000', 0, 1000, setSheetState),
                        _buildPriceChip('₹1,000 - ₹5,000', 1000, 5000, setSheetState),
                        _buildPriceChip('₹5,000 - ₹20,000', 5000, 20000, setSheetState),
                        _buildPriceChip('Over ₹20,000', 20000, null, setSheetState),
                      ],
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          _fetchProducts();
                        },
                        child: const Text('Apply Filters'),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildPriceChip(String label, double min, double? max, StateSetter setSheetState) {
    final isSelected = _minPrice == min && _maxPrice == max;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 12, color: isSelected ? Colors.white : AppColors.textPrimary)),
      selected: isSelected,
      selectedColor: AppColors.primary,
      onSelected: (val) {
        setSheetState(() {
          _minPrice = val ? min : null;
          _maxPrice = val ? max : null;
        });
      },
    );
  }
}
