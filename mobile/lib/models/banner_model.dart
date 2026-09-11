class BannerModel {
  final int id;
  final String title;
  final String? subtitle;
  final String imageUrl;
  final String? linkType;
  final String? linkId;
  final String ctaText;

  BannerModel({
    required this.id,
    required this.title,
    this.subtitle,
    required this.imageUrl,
    this.linkType,
    this.linkId,
    required this.ctaText,
  });

  factory BannerModel.fromJson(Map<String, dynamic> json) {
    return BannerModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] ?? '',
      subtitle: json['subtitle'],
      imageUrl: json['image_url'] ?? '',
      linkType: json['link_type'],
      linkId: json['link_id']?.toString(),
      ctaText: json['cta_text'] ?? 'Shop Now',
    );
  }
}
