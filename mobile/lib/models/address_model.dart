class AddressModel {
  final int id;
  final String fullName;
  final String phone;
  final String? alternatePhone;
  final String pincode;
  final String addressLine1;
  final String? addressLine2;
  final String? landmark;
  final String city;
  final String state;
  final String addressType;
  final bool isDefault;

  AddressModel({
    required this.id,
    required this.fullName,
    required this.phone,
    this.alternatePhone,
    required this.pincode,
    required this.addressLine1,
    this.addressLine2,
    this.landmark,
    required this.city,
    required this.state,
    required this.addressType,
    required this.isDefault,
  });

  factory AddressModel.fromJson(Map<String, dynamic> json) {
    return AddressModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      fullName: json['full_name'] ?? '',
      phone: json['phone'] ?? '',
      alternatePhone: json['alternate_phone'],
      pincode: json['pincode'] ?? '',
      addressLine1: json['address_line1'] ?? '',
      addressLine2: json['address_line2'],
      landmark: json['landmark'],
      city: json['city'] ?? '',
      state: json['state'] ?? '',
      addressType: json['address_type'] ?? 'HOME',
      isDefault: json['is_default'] == 1 || json['is_default'] == true,
    );
  }

  String get formattedAddress {
    final parts = [
      addressLine1,
      if (addressLine2 != null && addressLine2!.isNotEmpty) addressLine2,
      if (landmark != null && landmark!.isNotEmpty) 'Near $landmark',
      '$city, $state - $pincode'
    ];
    return parts.join(', ');
  }
}
