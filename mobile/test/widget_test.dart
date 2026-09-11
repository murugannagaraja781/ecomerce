import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flipkart_customer/main.dart';
import 'package:flipkart_customer/models/banner_model.dart';
import 'package:flipkart_customer/models/category_model.dart';
import 'package:flipkart_customer/models/product_model.dart';
import 'package:flipkart_customer/providers/catalog_provider.dart';

void main() {
  testWidgets('Flipkart App builds and displays main shell', (WidgetTester tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          homeFeedProvider.overrideWith((ref) async => {
                'banners': <BannerModel>[],
                'categories': <CategoryModel>[],
                'flash_deals': <ProductModel>[],
                'best_sellers': <ProductModel>[],
                'trending': <ProductModel>[],
              }),
        ],
        child: const FlipkartApp(),
      ),
    );
    await tester.pump();
    expect(find.byType(FlipkartApp), findsOneWidget);
  });
}
