import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../core/storage/token_storage.dart';
import '../models/user_model.dart';

class AuthRepository {
  final ApiClient _api = ApiClient();

  Future<Map<String, dynamic>> login(String emailOrPhone, String password) async {
    final response = await _api.post(ApiConstants.login, data: {
      'email': emailOrPhone,
      'password': password,
    });

    final data = response.data['data'];
    final tokens = data['tokens'];
    final user = UserModel.fromJson(data['user']);

    await TokenStorage.saveTokens(
      accessToken: tokens['access_token'],
      refreshToken: tokens['refresh_token'],
      role: user.role,
    );

    return {'user': user, 'tokens': tokens};
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    final response = await _api.post(ApiConstants.register, data: {
      'name': name,
      'email': email,
      'password': password,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    });

    final data = response.data['data'];
    final tokens = data['tokens'];
    final user = UserModel.fromJson(data['user']);

    await TokenStorage.saveTokens(
      accessToken: tokens['access_token'],
      refreshToken: tokens['refresh_token'],
      role: user.role,
    );

    return {'user': user, 'tokens': tokens};
  }

  Future<Map<String, dynamic>> sellerLogin(String email, String password) async {
    final response = await _api.post(ApiConstants.sellerLogin, data: {
      'email': email,
      'password': password,
    });
    final data = response.data['data'];
    final tokens = data['tokens'];
    final user = UserModel.fromJson(data['user']);

    await TokenStorage.saveTokens(
      accessToken: tokens['access_token'],
      refreshToken: tokens['refresh_token'],
      role: 'SELLER',
    );
    return {'user': user, 'seller': data['extra']?['seller']};
  }

  Future<Map<String, dynamic>> adminLogin(String email, String password) async {
    final response = await _api.post(ApiConstants.adminLogin, data: {
      'email': email,
      'password': password,
    });
    final data = response.data['data'];
    final tokens = data['tokens'];
    final user = UserModel.fromJson(data['user']);

    await TokenStorage.saveTokens(
      accessToken: tokens['access_token'],
      refreshToken: tokens['refresh_token'],
      role: user.role,
    );
    return {'user': user};
  }

  Future<UserModel?> getProfile() async {
    try {
      final response = await _api.get(ApiConstants.profile);
      if (response.data['success'] == true) {
        return UserModel.fromJson(response.data['data']['user']);
      }
    } catch (_) {}
    return null;
  }

  Future<void> logout() async {
    try {
      final refresh = await TokenStorage.getRefreshToken();
      await _api.post(ApiConstants.logout, data: {'refresh_token': refresh});
    } catch (_) {}
    await TokenStorage.clear();
  }
}
