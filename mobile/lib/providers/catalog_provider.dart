import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/category_model.dart';
import '../models/product_model.dart';
import '../repositories/catalog_repository.dart';

final catalogRepositoryProvider = Provider<CatalogRepository>((ref) => CatalogRepository());

final homeFeedProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final repo = ref.watch(catalogRepositoryProvider);
  return await repo.getHomeFeed();
});

final categoriesProvider = FutureProvider<List<CategoryModel>>((ref) async {
  final repo = ref.watch(catalogRepositoryProvider);
  return await repo.getCategories();
});

final productDetailProvider = FutureProvider.family<ProductModel, dynamic>((ref, idOrSlug) async {
  final repo = ref.watch(catalogRepositoryProvider);
  return await repo.getProductById(idOrSlug);
});
