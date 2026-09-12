import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../core/constants/api_constants.dart';

class BrandingModel {
  final String siteName;
  final String siteLogoUrl;
  final String siteFaviconUrl;
  final String siteTagline;
  final Color primaryColor;
  final Color secondaryColor;
  final String supportEmail;
  final String supportPhone;
  final String currencySymbol;
  final String footerCopyright;

  BrandingModel({
    required this.siteName,
    required this.siteLogoUrl,
    required this.siteFaviconUrl,
    required this.siteTagline,
    required this.primaryColor,
    required this.secondaryColor,
    required this.supportEmail,
    required this.supportPhone,
    required this.currencySymbol,
    required this.footerCopyright,
  });

  factory BrandingModel.fromJson(Map<String, dynamic> json) {
    Color hexToColor(String? hex, Color defaultColor) {
      if (hex == null || hex.isEmpty) return defaultColor;
      try {
        final buffer = StringBuffer();
        if (hex.length == 6 || hex.length == 7) buffer.write('ff');
        buffer.write(hex.replaceFirst('#', ''));
        return Color(int.parse(buffer.toString(), radix: 16));
      } catch (_) {
        return defaultColor;
      }
    }

    return BrandingModel(
      siteName: json['site_name'] ?? 'Store',
      siteLogoUrl: json['site_logo_url'] ?? '',
      siteFaviconUrl: json['site_favicon_url'] ?? '',
      siteTagline: json['site_tagline'] ?? 'Explore Plus ✦',
      primaryColor: hexToColor(json['primary_color'], const Color(0xFF2874F0)),
      secondaryColor: hexToColor(json['secondary_color'], const Color(0xFFFB641B)),
      supportEmail: json['support_email'] ?? 'support@store.local',
      supportPhone: json['support_phone'] ?? '1800 202 9898',
      currencySymbol: json['currency_symbol'] ?? '₹',
      footerCopyright: json['footer_copyright'] ?? '© 2026 Store. All Rights Reserved.',
    );
  }
}

class BrandingProvider extends ChangeNotifier {
  BrandingModel _branding = BrandingModel(
    siteName: 'Flipkart',
    siteLogoUrl: '',
    siteFaviconUrl: '',
    siteTagline: 'Explore Plus ✦',
    primaryColor: const Color(0xFF2874F0),
    secondaryColor: const Color(0xFFFB641B),
    supportEmail: 'support@store.local',
    supportPhone: '1800 202 9898',
    currencySymbol: '₹',
    footerCopyright: '© 2026 Store. All Rights Reserved.',
  );

  bool _isLoading = false;

  BrandingModel get branding => _branding;
  bool get isLoading => _isLoading;

  Future<void> fetchBranding() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/settings/public'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          _branding = BrandingModel.fromJson(data['data']);
        }
      }
    } catch (e) {
      debugPrint('Error loading branding in Mobile App: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
