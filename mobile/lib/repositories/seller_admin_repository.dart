import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';

class SellerAdminRepository {
  final ApiClient _api = ApiClient();

  // Seller APIs
  Future<Map<String, dynamic>> getSellerDashboard() async {
    final response = await _api.get(ApiConstants.sellerDashboard);
    return response.data['data'];
  }

  Future<List<dynamic>> getSellerProducts() async {
    final response = await _api.get(ApiConstants.sellerProducts);
    return response.data['data'] as List;
  }

  Future<void> createSellerProduct(Map<String, dynamic> productData) async {
    await _api.post(ApiConstants.sellerProducts, data: productData);
  }

  Future<List<dynamic>> getSellerInventory() async {
    final response = await _api.get(ApiConstants.sellerInventory);
    return response.data['data'] as List;
  }

  Future<void> updateStock(int inventoryId, int quantity) async {
    await _api.put('${ApiConstants.sellerInventory}/$inventoryId', data: {
      'quantity': quantity,
    });
  }

  Future<List<dynamic>> getSellerOrders() async {
    final response = await _api.get(ApiConstants.sellerOrders);
    return response.data['data'] as List;
  }

  Future<void> updateSellerOrderStatus(int orderId, String status) async {
    await _api.put('${ApiConstants.sellerOrders}/$orderId/status', data: {
      'status': status,
    });
  }

  // Admin APIs
  Future<Map<String, dynamic>> getAdminDashboard() async {
    final response = await _api.get(ApiConstants.adminDashboard);
    return response.data['data'];
  }

  Future<List<dynamic>> getAdminUsers() async {
    final response = await _api.get(ApiConstants.adminUsers);
    return response.data['data'] as List;
  }

  Future<void> updateUserStatus(int userId, bool isActive) async {
    await _api.put('${ApiConstants.adminUsers}/$userId', data: {
      'is_active': isActive ? 1 : 0,
    });
  }

  Future<List<dynamic>> getAdminSellers() async {
    final response = await _api.get(ApiConstants.adminSellers);
    return response.data['data'] as List;
  }

  Future<void> updateSellerVerification(int sellerId, String status) async {
    await _api.put('${ApiConstants.adminSellers}/$sellerId/status', data: {
      'status': status,
    });
  }

  Future<List<dynamic>> getAdminCategories() async {
    final response = await _api.get(ApiConstants.adminCategories);
    return response.data['data'] as List;
  }

  Future<void> createAdminCategory(Map<String, dynamic> data) async {
    await _api.post(ApiConstants.adminCategories, data: data);
  }

  Future<List<dynamic>> getAdminOrders() async {
    final response = await _api.get(ApiConstants.adminOrders);
    return response.data['data'] as List;
  }

  Future<void> updateAdminOrderStatus(int orderId, String status, String notes) async {
    await _api.put('${ApiConstants.adminOrders}/$orderId/status', data: {
      'status': status,
      'notes': notes,
    });
  }

  Future<List<dynamic>> getAdminPayments() async {
    final response = await _api.get(ApiConstants.adminPayments);
    return response.data['data'] as List;
  }

  Future<List<dynamic>> getAdminCoupons() async {
    final response = await _api.get(ApiConstants.adminCoupons);
    return response.data['data'] as List;
  }

  Future<void> createAdminCoupon(Map<String, dynamic> data) async {
    await _api.post(ApiConstants.adminCoupons, data: data);
  }

  Future<Map<String, dynamic>> getAdminReports() async {
    final response = await _api.get(ApiConstants.adminReports);
    return response.data['data'];
  }
}
