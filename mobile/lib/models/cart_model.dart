class CartItemModel {
  final int itemId;
  final int productId;
  final int? variantId;
  final int sellerId;
  final String productTitle;
  final String? variantTitle;
  final String? color;
  final String? sku;
  final double unitPrice;
  final double unitMrp;
  final int quantity;
  final double totalPrice;
  final double totalMrp;
  final int availableStock;
  final bool isInStock;
  final String? sellerName;
  final String? imageUrl;

  CartItemModel({
    required this.itemId,
    required this.productId,
    this.variantId,
    required this.sellerId,
    required this.productTitle,
    this.variantTitle,
    this.color,
    this.sku,
    required this.unitPrice,
    required this.unitMrp,
    required this.quantity,
    required this.totalPrice,
    required this.totalMrp,
    required this.availableStock,
    required this.isInStock,
    this.sellerName,
    this.imageUrl,
  });

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    return CartItemModel(
      itemId: json['item_id'] is int ? json['item_id'] : int.parse(json['item_id'].toString()),
      productId: json['product_id'] is int ? json['product_id'] : int.parse(json['product_id'].toString()),
      variantId: json['variant_id'] != null ? int.tryParse(json['variant_id'].toString()) : null,
      sellerId: json['seller_id'] is int ? json['seller_id'] : int.parse(json['seller_id'].toString()),
      productTitle: json['product_title'] ?? '',
      variantTitle: json['variant_title'],
      color: json['color'],
      sku: json['sku'],
      unitPrice: (num.tryParse(json['unit_price']?.toString() ?? '0') ?? 0).toDouble(),
      unitMrp: (num.tryParse(json['unit_mrp']?.toString() ?? '0') ?? 0).toDouble(),
      quantity: (num.tryParse(json['quantity']?.toString() ?? '1') ?? 1).toInt(),
      totalPrice: (num.tryParse(json['total_price']?.toString() ?? '0') ?? 0).toDouble(),
      totalMrp: (num.tryParse(json['total_mrp']?.toString() ?? '0') ?? 0).toDouble(),
      availableStock: (num.tryParse(json['available_stock']?.toString() ?? '0') ?? 0).toInt(),
      isInStock: json['is_in_stock'] == true || json['is_in_stock'] == 1,
      sellerName: json['seller_name'],
      imageUrl: json['image_url'],
    );
  }
}

class CartSummaryModel {
  final int totalItems;
  final double totalMrp;
  final double totalDiscount;
  final double deliveryCharge;
  final double subtotal;
  final double totalPayable;
  final double savings;
  final bool hasOutOfStock;

  CartSummaryModel({
    required this.totalItems,
    required this.totalMrp,
    required this.totalDiscount,
    required this.deliveryCharge,
    required this.subtotal,
    required this.totalPayable,
    required this.savings,
    required this.hasOutOfStock,
  });

  factory CartSummaryModel.fromJson(Map<String, dynamic> json) {
    return CartSummaryModel(
      totalItems: (num.tryParse(json['total_items']?.toString() ?? '0') ?? 0).toInt(),
      totalMrp: (num.tryParse(json['total_mrp']?.toString() ?? '0') ?? 0).toDouble(),
      totalDiscount: (num.tryParse(json['total_discount']?.toString() ?? '0') ?? 0).toDouble(),
      deliveryCharge: (num.tryParse(json['delivery_charge']?.toString() ?? '0') ?? 0).toDouble(),
      subtotal: (num.tryParse(json['subtotal']?.toString() ?? '0') ?? 0).toDouble(),
      totalPayable: (num.tryParse(json['total_payable']?.toString() ?? '0') ?? 0).toDouble(),
      savings: (num.tryParse(json['savings']?.toString() ?? '0') ?? 0).toDouble(),
      hasOutOfStock: json['has_out_of_stock'] == true || json['has_out_of_stock'] == 1,
    );
  }

  factory CartSummaryModel.empty() {
    return CartSummaryModel(
      totalItems: 0,
      totalMrp: 0,
      totalDiscount: 0,
      deliveryCharge: 0,
      subtotal: 0,
      totalPayable: 0,
      savings: 0,
      hasOutOfStock: false,
    );
  }
}
