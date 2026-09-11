class ProductModel {
  final int id;
  final String title;
  final String slug;
  final String? brandName;
  final String? categoryName;
  final String? sellerName;
  final double? sellerRating;
  final double baseMrp;
  final double basePrice;
  final int discountPercentage;
  final double rating;
  final int reviewCount;
  final String primaryImage;
  final String? description;
  final List<String> highlights;
  final Map<String, dynamic> specifications;
  final List<VariantModel> variants;
  final List<ProductImageModel> images;
  final List<ReviewModel> recentReviews;

  ProductModel({
    required this.id,
    required this.title,
    required this.slug,
    this.brandName,
    this.categoryName,
    this.sellerName,
    this.sellerRating,
    required this.baseMrp,
    required this.basePrice,
    required this.discountPercentage,
    required this.rating,
    required this.reviewCount,
    required this.primaryImage,
    this.description,
    this.highlights = const [],
    this.specifications = const {},
    this.variants = const [],
    this.images = const [],
    this.recentReviews = const [],
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    // Images
    List<ProductImageModel> imgs = [];
    if (json['images'] != null && json['images'] is List) {
      imgs = (json['images'] as List)
          .map((i) => ProductImageModel.fromJson(i))
          .toList();
    }

    // Variants
    List<VariantModel> vars = [];
    if (json['variants'] != null && json['variants'] is List) {
      vars = (json['variants'] as List)
          .map((v) => VariantModel.fromJson(v))
          .toList();
    }

    // Reviews
    List<ReviewModel> revs = [];
    if (json['recent_reviews'] != null && json['recent_reviews'] is List) {
      revs = (json['recent_reviews'] as List)
          .map((r) => ReviewModel.fromJson(r))
          .toList();
    }

    // Highlights
    List<String> hl = [];
    if (json['highlights'] != null) {
      if (json['highlights'] is List) {
        hl = List<String>.from(json['highlights'].map((x) => x.toString()));
      }
    }

    // Specs
    Map<String, dynamic> sp = {};
    if (json['specifications'] != null && json['specifications'] is Map) {
      sp = Map<String, dynamic>.from(json['specifications']);
    }

    final mrp = (num.tryParse(json['base_mrp']?.toString() ?? '0') ?? 0).toDouble();
    final price = (num.tryParse(json['base_price']?.toString() ?? '0') ?? 0).toDouble();
    final discount = (num.tryParse(json['discount_percentage']?.toString() ?? '0') ?? 
        (mrp > 0 ? (((mrp - price) / mrp) * 100).round() : 0)).toInt();

    return ProductModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] ?? '',
      slug: json['slug'] ?? '',
      brandName: json['brand_name'],
      categoryName: json['category_name'],
      sellerName: json['seller_name'] ?? json['store_name'],
      sellerRating: (num.tryParse(json['seller_rating']?.toString() ?? '') ?? 4.5).toDouble(),
      baseMrp: mrp,
      basePrice: price,
      discountPercentage: discount,
      rating: (num.tryParse(json['rating']?.toString() ?? '4.2') ?? 4.2).toDouble(),
      reviewCount: (num.tryParse(json['review_count']?.toString() ?? '0') ?? 0).toInt(),
      primaryImage: json['primary_image'] ?? 
          (imgs.isNotEmpty ? imgs.first.imageUrl : 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600'),
      description: json['description'],
      highlights: hl,
      specifications: sp,
      variants: vars,
      images: imgs,
      recentReviews: revs,
    );
  }
}

class VariantModel {
  final int id;
  final String sku;
  final String title;
  final String? color;
  final double price;
  final double mrp;
  final int stock;
  final String? imageUrl;

  VariantModel({
    required this.id,
    required this.sku,
    required this.title,
    this.color,
    required this.price,
    required this.mrp,
    required this.stock,
    this.imageUrl,
  });

  factory VariantModel.fromJson(Map<String, dynamic> json) {
    return VariantModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      sku: json['sku'] ?? '',
      title: json['title'] ?? '',
      color: json['color'],
      price: (num.tryParse(json['price']?.toString() ?? '0') ?? 0).toDouble(),
      mrp: (num.tryParse(json['mrp']?.toString() ?? '0') ?? 0).toDouble(),
      stock: (num.tryParse(json['stock']?.toString() ?? '0') ?? 0).toInt(),
      imageUrl: json['image_url'],
    );
  }
}

class ProductImageModel {
  final int id;
  final String imageUrl;
  final bool isPrimary;

  ProductImageModel({
    required this.id,
    required this.imageUrl,
    required this.isPrimary,
  });

  factory ProductImageModel.fromJson(Map<String, dynamic> json) {
    return ProductImageModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      imageUrl: json['image_url'] ?? '',
      isPrimary: json['is_primary'] == 1 || json['is_primary'] == true,
    );
  }
}

class ReviewModel {
  final int id;
  final int rating;
  final String title;
  final String comment;
  final String userName;
  final bool isVerifiedPurchase;
  final String? createdAt;

  ReviewModel({
    required this.id,
    required this.rating,
    required this.title,
    required this.comment,
    required this.userName,
    required this.isVerifiedPurchase,
    this.createdAt,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    return ReviewModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      rating: (num.tryParse(json['rating']?.toString() ?? '5') ?? 5).toInt(),
      title: json['title'] ?? '',
      comment: json['comment'] ?? '',
      userName: json['user_name'] ?? 'Verified Buyer',
      isVerifiedPurchase: json['is_verified_purchase'] == 1 || json['is_verified_purchase'] == true,
      createdAt: json['created_at'],
    );
  }
}
