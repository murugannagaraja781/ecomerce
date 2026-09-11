import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final NumberFormat _inrFormatter = NumberFormat.currency(
    locale: 'en_IN',
    symbol: '₹',
    decimalDigits: 0,
  );

  static String format(dynamic amount) {
    if (amount == null) return '₹0';
    final num val = amount is num ? amount : (num.tryParse(amount.toString()) ?? 0);
    return _inrFormatter.format(val);
  }
}
