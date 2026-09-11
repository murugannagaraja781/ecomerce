import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../core/responsive/responsive_layout.dart';
import '../../models/banner_model.dart';
import '../../models/category_model.dart';
import '../../models/product_model.dart';
import '../../providers/catalog_provider.dart';
import '../../widgets/product_card.dart';
import '../../widgets/search_app_bar.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final PageController _bannerController = PageController();
  int _currentBannerIndex = 0;
  Timer? _bannerTimer;

  @override
  void initState() {
    super.initState();
    _startBannerAutoScroll();
  }

  void _startBannerAutoScroll() {
    if (WidgetsBinding.instance.runtimeType.toString().contains('Test')) {
      return;
    }
    _bannerTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (_bannerController.hasClients) {
        _currentBannerIndex = (_currentBannerIndex + 1) % 4;
        _bannerController.animateToPage(
          _currentBannerIndex,
          duration: const Duration(milliseconds: 400),
          curve: Curves.easeInOut,
        );
      }
    });
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _bannerController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final homeAsync = ref.watch(homeFeedProvider);
    final isDesktop = ResponsiveLayout.isDesktop(context);

    return Scaffold(
      appBar: const SearchAppBar(),
      body: homeAsync.when(
        loading: () => const Center(
          child: CircularProgressIndicator(color: AppColors.primary),
        ),
        error: (err, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.cloud_off_outlined, size: 64, color: Colors.grey),
              const SizedBox(height: 12),
              Text('Unable to connect to Flipkart API: $err'),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () => ref.refresh(homeFeedProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
        data: (data) {
          final List<BannerModel> banners = data['banners'];
          final List<CategoryModel> categories = data['categories'];
          final List<ProductModel> flashDeals = data['flash_deals'];
          final List<ProductModel> bestSellers = data['best_sellers'];
          final List<ProductModel> trending = data['trending'];

          return RefreshIndicator(
            onRefresh: () async => ref.refresh(homeFeedProvider),
            child: SingleChildScrollView(
              padding: EdgeInsets.symmetric(
                horizontal: isDesktop ? 48 : 0,
                vertical: 8,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 1. Category Icons Carousel
                  _buildCategoriesStrip(categories),
                  const SizedBox(height: 8),

                  // 2. Hero Carousel Banners
                  _buildBannerCarousel(banners),
                  const SizedBox(height: 12),

                  // 3. Special Spotlight Deal: Celvas iPhone 15 Case
                  _buildCelvasSpotlightCard(context),
                  const SizedBox(height: 12),

                  // 4. Flash Deals Section (Horizontal Scroll)
                  _buildSectionHeader('Flash Deals', 'Min 40% Off', () {
                    context.push('/products');
                  }),
                  _buildHorizontalProductList(flashDeals),
                  const SizedBox(height: 12),

                  // 5. Trending in Mobiles & Electronics
                  _buildSectionHeader('Trending Gadgets', 'Top Rated', () {
                    context.push('/products');
                  }),
                  _buildHorizontalProductList(trending),
                  const SizedBox(height: 12),

                  // 6. Best Sellers Grid
                  _buildSectionHeader('Recommended For You', 'Popular Products', () {}),
                  _buildProductGrid(bestSellers, isDesktop),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildCategoriesStrip(List<CategoryModel> categories) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: SizedBox(
        height: 85,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          itemCount: categories.length,
          separatorBuilder: (_, __) => const SizedBox(width: 16),
          itemBuilder: (context, index) {
            final cat = categories[index];
            return GestureDetector(
              onTap: () => context.push('/products?category_id=${cat.id}&title=${Uri.encodeComponent(cat.name)}'),
              child: Column(
                children: [
                  Container(
                    width: 54,
                    height: 54,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: AppColors.primary.withOpacity(0.08),
                    ),
                    child: ClipOval(
                      child: cat.imageUrl != null
                          ? CachedNetworkImage(
                              imageUrl: cat.imageUrl!,
                              fit: BoxFit.cover,
                              errorWidget: (_, __, ___) => const Icon(
                                Icons.category,
                                color: AppColors.primary,
                                size: 24,
                              ),
                            )
                          : const Icon(Icons.category, color: AppColors.primary, size: 24),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    cat.name,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textPrimary,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildBannerCarousel(List<BannerModel> banners) {
    if (banners.isEmpty) return const SizedBox.shrink();
    return Column(
      children: [
        SizedBox(
          height: 180,
          child: PageView.builder(
            controller: _bannerController,
            onPageChanged: (idx) => setState(() => _currentBannerIndex = idx),
            itemCount: banners.length,
            itemBuilder: (context, index) {
              final b = banners[index];
              return GestureDetector(
                onTap: () {
                  if (b.linkType == 'PRODUCT' && b.linkId != null) {
                    context.push('/products/${b.linkId}');
                  } else {
                    context.push('/products');
                  }
                },
                child: Container(
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: CachedNetworkImage(
                      imageUrl: b.imageUrl,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: Colors.grey.shade200),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 6),
        // Indicators
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            banners.length,
            (i) => Container(
              width: _currentBannerIndex == i ? 16 : 6,
              height: 4,
              margin: const EdgeInsets.symmetric(horizontal: 2),
              decoration: BoxDecoration(
                color: _currentBannerIndex == i ? AppColors.primary : Colors.grey.shade400,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCelvasSpotlightCard(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E3C72), Color(0xFF2A5298)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Expanded(
            flex: 6,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.secondary,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'TRENDING DEAL',
                    style: TextStyle(
                      color: AppColors.primaryDark,
                      fontWeight: FontWeight.bold,
                      fontSize: 10,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Celvas Back Cover for Apple iPhone 15',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Military Grade Drop Protection • MagSafe Ready',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
                const SizedBox(height: 10),
                Row(
                  children: const [
                    Text(
                      '₹499',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(width: 8),
                    Text(
                      '₹1,499',
                      style: TextStyle(
                        color: Colors.white54,
                        fontSize: 13,
                        decoration: TextDecoration.lineThrough,
                      ),
                    ),
                    SizedBox(width: 8),
                    Text(
                      '67% Off',
                      style: TextStyle(
                        color: Color(0xFF69F0AE),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                ElevatedButton(
                  onPressed: () => context.push('/products/1'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.secondary,
                    foregroundColor: AppColors.textPrimary,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  ),
                  child: const Text('Shop Now →'),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 4,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: CachedNetworkImage(
                imageUrl: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400',
                height: 130,
                fit: BoxFit.cover,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title, String subtitle, VoidCallback onViewAll) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              Text(
                subtitle,
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.arrow_circle_right, color: AppColors.primary, size: 28),
            onPressed: onViewAll,
          ),
        ],
      ),
    );
  }

  Widget _buildHorizontalProductList(List<ProductModel> products) {
    return Container(
      color: Colors.white,
      height: 250,
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        itemCount: products.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final p = products[index];
          return SizedBox(
            width: 155,
            child: ProductCard(
              product: p,
              onTap: () => context.push('/products/${p.id}'),
            ),
          );
        },
      ),
    );
  }

  Widget _buildProductGrid(List<ProductModel> products, bool isDesktop) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: products.length,
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: isDesktop ? 5 : 2,
          childAspectRatio: 0.65,
          crossAxisSpacing: 8,
          mainAxisSpacing: 8,
        ),
        itemBuilder: (context, index) {
          final p = products[index];
          return ProductCard(
            product: p,
            onTap: () => context.push('/products/${p.id}'),
          );
        },
      ),
    );
  }
}
