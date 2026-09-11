class OrderModel {
  final int id;
  final String orderNumber;
  final String status;
  final double totalPayable;
  final String paymentStatus;
  final String paymentMethod;
  final String? expectedDeliveryDate;
  final String? deliveredAt;
  final String createdAt;
  final String? sampleProductTitle;
  final String? sampleProductImage;
  final int totalItems;
  final List<OrderItemModel> items;
  final List<TrackingMilestoneModel> milestones;
  final Map<String, dynamic>? deliveryAddress;

  OrderModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.totalPayable,
    required this.paymentStatus,
    required this.paymentMethod,
    this.expectedDeliveryDate,
    this.deliveredAt,
    required this.createdAt,
    this.sampleProductTitle,
    this.sampleProductImage,
    this.totalItems = 1,
    this.items = const [],
    this.milestones = const [],
    this.deliveryAddress,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    List<OrderItemModel> oitems = [];
    if (json['items'] != null && json['items'] is List) {
      oitems = (json['items'] as List)
          .map((i) => OrderItemModel.fromJson(i))
          .toList();
    }

    List<TrackingMilestoneModel> ms = [];
    if (json['tracking_milestones'] != null && json['tracking_milestones'] is List) {
      ms = (json['tracking_milestones'] as List)
          .map((m) => TrackingMilestoneModel.fromJson(m))
          .toList();
    }

    return OrderModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderNumber: json['order_number'] ?? '',
      status: json['status'] ?? 'PLACED',
      totalPayable: (num.tryParse(json['total_payable']?.toString() ?? '0') ?? 0).toDouble(),
      paymentStatus: json['payment_status'] ?? 'PENDING',
      paymentMethod: json['payment_method'] ?? 'RAZORPAY',
      expectedDeliveryDate: json['expected_delivery_date'],
      deliveredAt: json['delivered_at'],
      createdAt: json['created_at'] ?? '',
      sampleProductTitle: json['sample_product_title'] ?? (oitems.isNotEmpty ? oitems.first.productTitle : 'Order Item'),
      sampleProductImage: json['sample_product_image'] ?? (oitems.isNotEmpty ? oitems.first.imageUrl : null),
      totalItems: (num.tryParse(json['total_items']?.toString() ?? '1') ?? (oitems.isNotEmpty ? oitems.length : 1)).toInt(),
      items: oitems,
      milestones: ms,
      deliveryAddress: json['delivery_address'] != null && json['delivery_address'] is Map ? Map<String, dynamic>.from(json['delivery_address']) : null,
    );
  }
}

class OrderItemModel {
  final int id;
  final int productId;
  final int? variantId;
  final String productTitle;
  final String? variantTitle;
  final double price;
  final double mrp;
  final int quantity;
  final double totalPrice;
  final String? imageUrl;

  OrderItemModel({
    required this.id,
    required this.productId,
    this.variantId,
    required this.productTitle,
    this.variantTitle,
    required this.price,
    required this.mrp,
    required this.quantity,
    required this.totalPrice,
    this.imageUrl,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      productId: json['product_id'] is int ? json['product_id'] : int.parse(json['product_id'].toString()),
      variantId: json['variant_id'] != null ? int.tryParse(json['variant_id'].toString()) : null,
      productTitle: json['product_title'] ?? '',
      variantTitle: json['variant_title'],
      price: (num.tryParse(json['price']?.toString() ?? '0') ?? 0).toDouble(),
      mrp: (num.tryParse(json['mrp']?.toString() ?? '0') ?? 0).toDouble(),
      quantity: (num.tryParse(json['quantity']?.toString() ?? '1') ?? 1).toInt(),
      totalPrice: (num.tryParse(json['total_price']?.toString() ?? '0') ?? 0).toDouble(),
      imageUrl: json['image_url'],
    );
  }
}

class TrackingMilestoneModel {
  final String title;
  final bool completed;
  final String? date;
  final String? notes;

  TrackingMilestoneModel({
    required this.title,
    required this.completed,
    this.date,
    this.notes,
  });

  factory TrackingMilestoneModel.fromJson(Map<String, dynamic> json) {
    return TrackingMilestoneModel(
      title: json['title'] ?? '',
      completed: json['completed'] == true || json['completed'] == 1,
      date: json['date'],
      notes: json['notes'],
    );
  }
}
