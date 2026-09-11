import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../core/responsive/responsive_layout.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/product_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/catalog_provider.dart';
import '../../widgets/custom_badge.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  final dynamic productId;

  const ProductDetailScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  int _currentImageIndex = 0;
  VariantModel? _selectedVariant;
  bool _isAddingToCart = false;

  @override
  Widget build(BuildContext context) {
    final productAsync = ref.watch(productDetailProvider(widget.productId));
    final cartState = ref.watch(cartProvider);
    final isDesktop = ResponsiveLayout.isDesktop(context);

    return productAsync.when(
      loading: () => const Scaffold(
        body: Center(child: CircularProgressIndicator(color: AppColors.primary)),
      ),
      error: (err, _) => Scaffold(
        appBar: AppBar(title: const Text('Product Details')),
        body: Center(child: Text('Error loading product: $err')),
      ),
      data: (product) {
        if (_selectedVariant == null && product.variants.isNotEmpty) {
          _selectedVariant = product.variants.first;
        }

        final currentPrice = _selectedVariant?.price ?? product.basePrice;
        final currentMrp = _selectedVariant?.mrp ?? product.baseMrp;
        final discount = currentMrp > 0
            ? (((currentMrp - currentPrice) / currentMrp) * 100).round()
            : 0;

        return Scaffold(
          appBar: AppBar(
            title: Text(product.title, maxLines: 1, overflow: TextOverflow.ellipsis),
            actions: [
              IconButton(
                icon: const Icon(Icons.share_outlined),
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Product link copied to clipboard!')),
                  );
                },
              ),
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined),
                    onPressed: () => context.push('/cart'),
                  ),
                  if (cartState.badgeCount > 0)
                    Positioned(
                      top: 4,
                      right: 4,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: AppColors.accentOrange,
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                        child: Center(
                          child: Text(
                            '${cartState.badgeCount}',
                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
          body: SingleChildScrollView(
            child: Center(
              child: ConstrainedBox(
                constraints: BoxConstraints(maxWidth: isDesktop ? 1000 : double.infinity),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. Image Gallery
                    _buildImageGallery(product),
                    const SizedBox(height: 12),

                    // 2. Title & Brand
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (product.brandName != null)
                            Text(
                              product.brandName!.toUpperCase(),
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: AppColors.primary,
                                letterSpacing: 0.5,
                              ),
                            ),
                          const SizedBox(height: 4),
                          Text(
                            product.title,
                            style: const TextStyle(
                              fontSize: 17,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textPrimary,
                              height: 1.3,
                            ),
                          ),
                          const SizedBox(height: 8),

                          // Rating pill + review count + assured badge
                          Row(
                            children: [
                              RatingBadge(rating: product.rating),
                              const SizedBox(width: 8),
                              Text(
                                '${product.reviewCount} Ratings & Reviews',
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: AppColors.textSecondary,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE8F0FE),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Row(
                                  children: const [
                                    Text('f-Assured', style: TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.bold, fontStyle: FontStyle.italic)),
                                    Icon(Icons.check_circle, color: AppColors.primary, size: 12),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),

                          // Price, MRP, Discount
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.baseline,
                            textBaseline: TextBaseline.alphabetic,
                            children: [
                              Text(
                                CurrencyFormatter.format(currentPrice),
                                style: const TextStyle(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Text(
                                CurrencyFormatter.format(currentMrp),
                                style: const TextStyle(
                                  fontSize: 16,
                                  color: AppColors.textMuted,
                                  decoration: TextDecoration.lineThrough,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Text(
                                '$discount% off',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.successGreen,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Divider(height: 1, thickness: 6, color: AppColors.divider),

                    // 3. Variant Selector (Color / Model)
                    if (product.variants.isNotEmpty)
                      _buildVariantSelector(product),

                    // 4. Available Offers & Coupons
                    _buildAvailableOffers(),
                    const Divider(height: 1, thickness: 6, color: AppColors.divider),

                    // 5. Highlights
                    if (product.highlights.isNotEmpty)
                      _buildHighlights(product),

                    // 6. Seller Details
                    _buildSellerInfo(product),
                    const Divider(height: 1, thickness: 6, color: AppColors.divider),

                    // 7. Specifications
                    if (product.specifications.isNotEmpty)
                      _buildSpecifications(product),

                    // 8. Description
                    if (product.description != null && product.description!.isNotEmpty)
                      _buildDescription(product),

                    // 9. Customer Reviews
                    _buildReviewsSection(product),
                    const SizedBox(height: 80), // Padding for sticky bottom bar
                  ],
                ),
              ),
            ),
          ),
          // Sticky Bottom Bar: ADD TO CART & BUY NOW
          bottomNavigationBar: _buildBottomActions(product),
        );
      },
    );
  }

  Widget _buildImageGallery(ProductModel product) {
    final images = product.images.isNotEmpty
        ? product.images.map((i) => i.imageUrl).toList()
        : [product.primaryImage];

    return Container(
      color: Colors.white,
      height: 320,
      child: Column(
        children: [
          Expanded(
            child: PageView.builder(
              itemCount: images.length,
              onPageChanged: (idx) => setState(() => _currentImageIndex = idx),
              itemBuilder: (context, index) {
                return Padding(
                  padding: const EdgeInsets.all(16),
                  child: CachedNetworkImage(
                    imageUrl: images[index],
                    fit: BoxFit.contain,
                    placeholder: (_, __) => const Center(child: CircularProgressIndicator(strokeWidth: 2)),
                    errorWidget: (_, __, ___) => const Icon(Icons.image_not_supported, size: 48, color: Colors.grey),
                  ),
                );
              },
            ),
          ),
          // Indicator dots
          if (images.length > 1)
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                images.length,
                (i) => Container(
                  width: _currentImageIndex == i ? 16 : 6,
                  height: 6,
                  margin: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    color: _currentImageIndex == i ? AppColors.primary : Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
            ),
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _buildVariantSelector(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Select Variant / Color:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            children: product.variants.map((v) {
              final isSelected = _selectedVariant?.id == v.id;
              return ChoiceChip(
                label: Text(
                  v.color ?? v.title,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                    color: isSelected ? Colors.white : AppColors.textPrimary,
                  ),
                ),
                selected: isSelected,
                selectedColor: AppColors.primary,
                onSelected: (val) {
                  if (val) setState(() => _selectedVariant = v);
                },
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildAvailableOffers() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Available Offers', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          _offerItem('Bank Offer: 5% Unlimited Cashback on Axis Bank Credit Card'),
          _offerItem('Special Promo: Get extra ₹100 instant off with coupon WELCOME100'),
          _offerItem('Partner Offer: Free ₹250 Gift Card on Flipkart Pay Later signup'),
        ],
      ),
    );
  }

  Widget _offerItem(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.local_offer, color: AppColors.successGreen, size: 16),
          const SizedBox(width: 8),
          Expanded(child: Text(text, style: const TextStyle(fontSize: 12, height: 1.3))),
        ],
      ),
    );
  }

  Widget _buildHighlights(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Product Highlights', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          ...product.highlights.map((h) => Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('• ', style: TextStyle(fontWeight: FontWeight.bold)),
                    Expanded(child: Text(h, style: const TextStyle(fontSize: 13, height: 1.3))),
                  ],
                ),
              )),
        ],
      ),
    );
  }

  Widget _buildSellerInfo(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Sold by', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
              Text(
                product.sellerName ?? 'Celvas Official Store',
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.primary),
              ),
              const SizedBox(height: 2),
              const Text('7 Days Replacement Policy • 1 Year Warranty', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
            ],
          ),
          RatingBadge(rating: product.sellerRating ?? 4.8),
        ],
      ),
    );
  }

  Widget _buildSpecifications(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Specifications', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Table(
            border: TableBorder.all(color: AppColors.border, width: 0.5),
            children: product.specifications.entries.map((e) {
              return TableRow(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(8),
                    child: Text(e.key, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8),
                    child: Text(e.value.toString(), style: const TextStyle(fontSize: 12, color: AppColors.textPrimary)),
                  ),
                ],
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildDescription(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Product Description', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Text(
            product.description!,
            style: const TextStyle(fontSize: 13, height: 1.4, color: AppColors.textPrimary),
          ),
        ],
      ),
    );
  }

  Widget _buildReviewsSection(ProductModel product) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Ratings & Reviews', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
              RatingBadge(rating: product.rating),
            ],
          ),
          const SizedBox(height: 12),
          if (product.recentReviews.isEmpty)
            const Text('No reviews yet. Be the first to review!', style: TextStyle(color: Colors.grey))
          else
            ...product.recentReviews.map((r) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          RatingBadge(rating: r.rating.toDouble()),
                          const SizedBox(width: 8),
                          Text(r.title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(r.comment, style: const TextStyle(fontSize: 12, color: AppColors.textPrimary)),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Text(r.userName, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                          const SizedBox(width: 6),
                          const Icon(Icons.verified, color: AppColors.successGreen, size: 12),
                          const Text(' Verified Buyer', style: TextStyle(fontSize: 11, color: AppColors.successGreen)),
                        ],
                      ),
                      const Divider(height: 16),
                    ],
                  ),
                )),
        ],
      ),
    );
  }

  Widget _buildBottomActions(ProductModel product) {
    return Container(
      height: 60,
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: Row(
        children: [
          // ADD TO CART BUTTON
          Expanded(
            child: SizedBox(
              height: double.infinity,
              child: ElevatedButton(
                onPressed: _isAddingToCart ? null : () => _handleAddToCart(product),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: AppColors.textPrimary,
                  shape: const RoundedRectangleBorder(),
                ),
                child: _isAddingToCart
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Icon(Icons.shopping_cart, size: 18, color: AppColors.textPrimary),
                          SizedBox(width: 6),
                          Text('ADD TO CART', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                        ],
                      ),
              ),
            ),
          ),
          // BUY NOW BUTTON
          Expanded(
            child: SizedBox(
              height: double.infinity,
              child: ElevatedButton(
                onPressed: () async {
                  await _handleAddToCart(product);
                  if (mounted) context.push('/cart');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.accentOrange,
                  foregroundColor: Colors.white,
                  shape: const RoundedRectangleBorder(),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: const [
                    Icon(Icons.flash_on, size: 18, color: Colors.white),
                    SizedBox(width: 6),
                    Text('BUY NOW', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _handleAddToCart(ProductModel product) async {
    setState(() => _isAddingToCart = true);
    final success = await ref.read(cartProvider.notifier).addToCart(
          product.id,
          variantId: _selectedVariant?.id,
          quantity: 1,
        );
    setState(() => _isAddingToCart = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(success ? 'Added to Cart!' : 'Failed to add item'),
          backgroundColor: success ? AppColors.successGreen : AppColors.error,
          action: SnackBarAction(
            label: 'VIEW CART',
            textColor: Colors.white,
            onPressed: () => context.push('/cart'),
          ),
        ),
      );
    }
  }
}
