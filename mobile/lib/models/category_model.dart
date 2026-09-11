class CategoryModel {
  final int id;
  final int? parentId;
  final String name;
  final String slug;
  final String? imageUrl;
  final String? description;
  final List<CategoryModel> subcategories;

  CategoryModel({
    required this.id,
    this.parentId,
    required this.name,
    required this.slug,
    this.imageUrl,
    this.description,
    this.subcategories = const [],
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    List<CategoryModel> subs = [];
    if (json['subcategories'] != null && json['subcategories'] is List) {
      subs = (json['subcategories'] as List)
          .map((s) => CategoryModel.fromJson(s))
          .toList();
    }

    return CategoryModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      parentId: json['parent_id'] != null ? int.tryParse(json['parent_id'].toString()) : null,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      imageUrl: json['image_url'],
      description: json['description'],
      subcategories: subs,
    );
  }
}
