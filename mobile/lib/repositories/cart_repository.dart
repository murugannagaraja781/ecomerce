import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/cart_model.dart';
import '../models/product_model.dart';

class CartRepository {
  final ApiClient _api = ApiClient();

  Future<Map<String, dynamic>> getCart() async {
    final response = await _api.get(ApiConstants.cart);
    final data = response.data['data'];

    final items = (data['items'] as List)
        .map((i) => CartItemModel.fromJson(i))
        .toList();

    final summary = CartSummaryModel.fromJson(data['summary']);

    return {
      'items': items,
      'summary': summary,
    };
  }

  Future<Map<String, dynamic>> addItem({
    required int productId,
    int? variantId,
    int quantity = 1,
  }) async {
    final response = await _api.post(ApiConstants.cartItems, data: {
      'product_id': productId,
      if (variantId != null) 'variant_id': variantId,
      'quantity': quantity,
    });

    final data = response.data['data'];
    final items = (data['items'] as List)
        .map((i) => CartItemModel.fromJson(i))
        .toList();

    final summary = CartSummaryModel.fromJson(data['summary']);
    return {'items': items, 'summary': summary};
  }

  Future<Map<String, dynamic>> updateQuantity(int itemId, int quantity) async {
    final response = await _api.put('${ApiConstants.cartItems}/$itemId', data: {
      'quantity': quantity,
    });

    final data = response.data['data'];
    final items = (data['items'] as List)
        .map((i) => CartItemModel.fromJson(i))
        .toList();

    final summary = CartSummaryModel.fromJson(data['summary']);
    return {'items': items, 'summary': summary};
  }

  Future<Map<String, dynamic>> removeItem(int itemId) async {
    final response = await _api.delete('${ApiConstants.cartItems}/$itemId');
    final data = response.data['data'];
    final items = (data['items'] as List)
        .map((i) => CartItemModel.fromJson(i))
        .toList();
    final summary = CartSummaryModel.fromJson(data['summary']);
    return {'items': items, 'summary': summary};
  }

  Future<List<ProductModel>> getWishlist() async {
    final response = await _api.get(ApiConstants.wishlist);
    final list = response.data['data'] as List;
    return list.map((w) => ProductModel.fromJson(w)).toList();
  }

  Future<bool> addToWishlist(int productId, {int? variantId}) async {
    final response = await _api.post(ApiConstants.wishlist, data: {
      'product_id': productId,
      if (variantId != null) 'variant_id': variantId,
    });
    return response.data['success'] == true;
  }

  Future<bool> removeFromWishlist(int id) async {
    final response = await _api.delete('${ApiConstants.wishlist}/$id');
    return response.data['success'] == true;
  }
}
