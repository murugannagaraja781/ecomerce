import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/banner_model.dart';
import '../models/category_model.dart';
import '../models/product_model.dart';

class CatalogRepository {
  final ApiClient _api = ApiClient();

  Future<Map<String, dynamic>> getHomeFeed() async {
    final response = await _api.get(ApiConstants.homeFeed);
    final data = response.data['data'];

    final banners = (data['banners'] as List)
        .map((b) => BannerModel.fromJson(b))
        .toList();

    final categories = (data['categories'] as List)
        .map((c) => CategoryModel.fromJson(c))
        .toList();

    final flashDeals = (data['flash_deals'] as List)
        .map((p) => ProductModel.fromJson(p))
        .toList();

    final bestSellers = (data['best_sellers'] as List)
        .map((p) => ProductModel.fromJson(p))
        .toList();

    final trending = (data['trending_electronics'] as List)
        .map((p) => ProductModel.fromJson(p))
        .toList();

    return {
      'banners': banners,
      'categories': categories,
      'flash_deals': flashDeals,
      'best_sellers': bestSellers,
      'trending': trending,
    };
  }

  Future<List<CategoryModel>> getCategories() async {
    final response = await _api.get(ApiConstants.categories);
    final list = response.data['data'] as List;
    return list.map((c) => CategoryModel.fromJson(c)).toList();
  }

  Future<Map<String, dynamic>> getProducts({
    int? categoryId,
    int? brandId,
    double? minPrice,
    double? maxPrice,
    int? minRating,
    String? sort,
    int page = 1,
    int limit = 20,
  }) async {
    final query = <String, dynamic>{
      'page': page,
      'limit': limit,
      if (categoryId != null) 'category_id': categoryId,
      if (brandId != null) 'brand_id': brandId,
      if (minPrice != null) 'min_price': minPrice,
      if (maxPrice != null) 'max_price': maxPrice,
      if (minRating != null) 'min_rating': minRating,
      if (sort != null) 'sort': sort,
    };

    final response = await _api.get(ApiConstants.products, queryParameters: query);
    final items = (response.data['data'] as List)
        .map((p) => ProductModel.fromJson(p))
        .toList();

    return {
      'products': items,
      'pagination': response.data['pagination'],
    };
  }

  Future<ProductModel> getProductById(dynamic idOrSlug) async {
    final response = await _api.get('${ApiConstants.products}/$idOrSlug');
    return ProductModel.fromJson(response.data['data']);
  }

  Future<Map<String, dynamic>> search(
    String query, {
    int page = 1,
    String? sort,
    int? categoryId,
    int? brandId,
  }) async {
    final q = <String, dynamic>{
      'q': query,
      'page': page,
      if (sort != null) 'sort': sort,
      if (categoryId != null) 'category_id': categoryId,
      if (brandId != null) 'brand_id': brandId,
    };

    final response = await _api.get(ApiConstants.search, queryParameters: q);
    final items = (response.data['data'] as List)
        .map((p) => ProductModel.fromJson(p))
        .toList();

    return {
      'products': items,
      'pagination': response.data['pagination'],
    };
  }

  Future<List<Map<String, dynamic>>> getSuggestions(String query) async {
    final response = await _api.get(ApiConstants.suggestions, queryParameters: {'q': query});
    final list = response.data['data']['suggestions'] as List;
    return List<Map<String, dynamic>>.from(list);
  }
}
