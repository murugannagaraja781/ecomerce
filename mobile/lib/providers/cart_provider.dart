import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/cart_model.dart';
import '../repositories/cart_repository.dart';

class CartState {
  final bool isLoading;
  final List<CartItemModel> items;
  final CartSummaryModel summary;
  final String? error;

  CartState({
    this.isLoading = false,
    this.items = const [],
    required this.summary,
    this.error,
  });

  int get badgeCount => summary.totalItems;

  CartState copyWith({
    bool? isLoading,
    List<CartItemModel>? items,
    CartSummaryModel? summary,
    String? error,
  }) {
    return CartState(
      isLoading: isLoading ?? this.isLoading,
      items: items ?? this.items,
      summary: summary ?? this.summary,
      error: error,
    );
  }
}

class CartNotifier extends StateNotifier<CartState> {
  final CartRepository _repo;

  CartNotifier(this._repo) : super(CartState(isLoading: false, summary: CartSummaryModel.empty()));

  Future<void> loadCart() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.getCart();
      state = state.copyWith(
        isLoading: false,
        items: res['items'],
        summary: res['summary'],
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<bool> addToCart(int productId, {int? variantId, int quantity = 1}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.addItem(
        productId: productId,
        variantId: variantId,
        quantity: quantity,
      );
      state = state.copyWith(
        isLoading: false,
        items: res['items'],
        summary: res['summary'],
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<void> updateQuantity(int itemId, int quantity) async {
    try {
      final res = await _repo.updateQuantity(itemId, quantity);
      state = state.copyWith(
        items: res['items'],
        summary: res['summary'],
      );
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }

  Future<void> removeItem(int itemId) async {
    try {
      final res = await _repo.removeItem(itemId);
      state = state.copyWith(
        items: res['items'],
        summary: res['summary'],
      );
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }
}

final cartRepositoryProvider = Provider<CartRepository>((ref) => CartRepository());

final cartProvider = StateNotifierProvider<CartNotifier, CartState>((ref) {
  return CartNotifier(ref.watch(cartRepositoryProvider));
});
