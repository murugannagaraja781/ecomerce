import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/address_model.dart';
import '../models/order_model.dart';

class CheckoutRepository {
  final ApiClient _api = ApiClient();

  Future<List<AddressModel>> getAddresses() async {
    final response = await _api.get(ApiConstants.addresses);
    final list = response.data['data'] as List;
    return list.map((a) => AddressModel.fromJson(a)).toList();
  }

  Future<int> createAddress({
    required String fullName,
    required String phone,
    required String pincode,
    required String addressLine1,
    String? addressLine2,
    String? landmark,
    required String city,
    required String state,
    String addressType = 'HOME',
    bool isDefault = false,
  }) async {
    final response = await _api.post(ApiConstants.addresses, data: {
      'full_name': fullName,
      'phone': phone,
      'pincode': pincode,
      'address_line1': addressLine1,
      if (addressLine2 != null) 'address_line2': addressLine2,
      if (landmark != null) 'landmark': landmark,
      'city': city,
      'state': state,
      'address_type': addressType,
      'is_default': isDefault ? 1 : 0,
    });
    return response.data['data']['id'];
  }

  Future<Map<String, dynamic>> applyCoupon(String code, double cartAmount) async {
    final response = await _api.post(ApiConstants.applyCoupon, data: {
      'code': code,
      'cart_amount': cartAmount,
    });
    return response.data['data'];
  }

  Future<Map<String, dynamic>> createPaymentOrder(int addressId, String? couponCode) async {
    final response = await _api.post(ApiConstants.paymentCreate, data: {
      'address_id': addressId,
      if (couponCode != null && couponCode.isNotEmpty) 'coupon_code': couponCode,
    });
    return response.data['data'];
  }

  Future<Map<String, dynamic>> verifyPaymentAndPlaceOrder({
    required int addressId,
    required String razorpayOrderId,
    required String razorpayPaymentId,
    required String razorpaySignature,
    String? couponCode,
    String paymentMethod = 'RAZORPAY',
  }) async {
    final response = await _api.post(ApiConstants.paymentVerify, data: {
      'address_id': addressId,
      'razorpay_order_id': razorpayOrderId,
      'razorpay_payment_id': razorpayPaymentId,
      'razorpay_signature': razorpaySignature,
      if (couponCode != null) 'coupon_code': couponCode,
      'payment_method': paymentMethod,
    });
    return response.data['data'];
  }

  Future<List<OrderModel>> getOrders({String? status}) async {
    final query = status != null ? {'status': status} : null;
    final response = await _api.get(ApiConstants.orders, queryParameters: query);
    final list = response.data['data'] as List;
    return list.map((o) => OrderModel.fromJson(o)).toList();
  }

  Future<OrderModel> getOrderById(dynamic id) async {
    final response = await _api.get('${ApiConstants.orders}/$id');
    return OrderModel.fromJson(response.data['data']);
  }

  Future<void> cancelOrder(int orderId, String reason) async {
    await _api.post('${ApiConstants.orders}/$orderId/cancel', data: {
      'reason': reason,
    });
  }

  Future<void> submitReview({
    required int productId,
    required int rating,
    required String title,
    required String comment,
  }) async {
    await _api.post(ApiConstants.reviews, data: {
      'product_id': productId,
      'rating': rating,
      'title': title,
      'comment': comment,
    });
  }
}
