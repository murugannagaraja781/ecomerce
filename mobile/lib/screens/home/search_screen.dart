import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/catalog_provider.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  Timer? _debounceTimer;
  List<Map<String, dynamic>> _suggestions = [];
  bool _isLoading = false;

  final List<String> _popularSearches = [
    'iPhone 15 Case',
    'Apple iPhone 15',
    'boAt Airdopes',
    'Smart Watches',
    'Running Shoes',
    'Cotton Shirts',
    '4K Smart TV',
    'Face Serum',
  ];

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged(String query) {
    _debounceTimer?.cancel();
    if (query.trim().length < 2) {
      setState(() {
        _suggestions = [];
        _isLoading = false;
      });
      return;
    }

    setState(() => _isLoading = true);
    _debounceTimer = Timer(const Duration(milliseconds: 300), () async {
      final repo = ref.read(catalogRepositoryProvider);
      try {
        final list = await repo.getSuggestions(query.trim());
        if (mounted) {
          setState(() {
            _suggestions = list;
            _isLoading = false;
          });
        }
      } catch (_) {
        if (mounted) setState(() => _isLoading = false);
      }
    });
  }

  void _performSearch(String query) {
    if (query.trim().isEmpty) return;
    context.push('/products?q=${Uri.encodeComponent(query.trim())}');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: Container(
          height: 40,
          margin: const EdgeInsets.only(right: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(4),
          ),
          child: TextField(
            controller: _searchController,
            autofocus: true,
            onChanged: _onSearchChanged,
            onSubmitted: _performSearch,
            decoration: InputDecoration(
              hintText: 'Search for products, brands and more',
              hintStyle: const TextStyle(color: Colors.grey, fontSize: 13),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              suffixIcon: _isLoading
                  ? const Padding(
                      padding: EdgeInsets.all(10),
                      child: SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)),
                    )
                  : _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18, color: Colors.grey),
                          onPressed: () {
                            _searchController.clear();
                            _onSearchChanged('');
                          },
                        )
                      : const Icon(Icons.mic_none, size: 18, color: Colors.grey),
            ),
          ),
        ),
      ),
      body: _suggestions.isNotEmpty
          ? ListView.separated(
              itemCount: _suggestions.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final s = _suggestions[index];
                return ListTile(
                  leading: const Icon(Icons.search, color: Colors.grey, size: 20),
                  title: Text(s['text'] ?? '', style: const TextStyle(fontSize: 14)),
                  trailing: Text(
                    (s['type'] ?? '').toString().toUpperCase(),
                    style: const TextStyle(fontSize: 10, color: AppColors.primary, fontWeight: FontWeight.bold),
                  ),
                  onTap: () => _performSearch(s['text']),
                );
              },
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('POPULAR SEARCHES', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _popularSearches.map((term) {
                      return ActionChip(
                        avatar: const Icon(Icons.trending_up, size: 16, color: AppColors.primary),
                        label: Text(term, style: const TextStyle(fontSize: 12)),
                        onPressed: () => _performSearch(term),
                      );
                    }).toList(),
                  ),
                ],
              ),
            ),
    );
  }
}
