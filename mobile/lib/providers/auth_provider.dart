import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/user_model.dart';
import '../repositories/auth_repository.dart';
import '../core/storage/token_storage.dart';

class AuthState {
  final bool isLoading;
  final bool isAuthenticated;
  final UserModel? user;
  final String? error;

  AuthState({
    this.isLoading = false,
    this.isAuthenticated = false,
    this.user,
    this.error,
  });

  AuthState copyWith({
    bool? isLoading,
    bool? isAuthenticated,
    UserModel? user,
    String? error,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      user: user ?? this.user,
      error: error,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _repo;

  AuthNotifier(this._repo) : super(AuthState(isLoading: true)) {
    checkAuth();
  }

  Future<void> checkAuth() async {
    final token = await TokenStorage.getAccessToken();
    if (token != null && token.isNotEmpty) {
      final user = await _repo.getProfile();
      if (user != null) {
        state = state.copyWith(isLoading: false, isAuthenticated: true, user: user);
        return;
      }
    }
    state = state.copyWith(isLoading: false, isAuthenticated: false, user: null);
  }

  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.login(email, password);
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        user: res['user'],
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
      );
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        user: res['user'],
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> sellerLogin(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.sellerLogin(email, password);
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        user: res['user'],
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<bool> adminLogin(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _repo.adminLogin(email, password);
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        user: res['user'],
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<void> logout() async {
    await _repo.logout();
    state = AuthState(isAuthenticated: false, user: null);
  }
}

final authRepositoryProvider = Provider<AuthRepository>((ref) => AuthRepository());

final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.watch(authRepositoryProvider));
});
