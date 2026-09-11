import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';

class RatingBadge extends StatelessWidget {
  final double rating;
  final double fontSize;
  final double iconSize;

  const RatingBadge({
    super.key,
    required this.rating,
    this.fontSize = 11,
    this.iconSize = 11,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.ratingGreen,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            rating.toStringAsFixed(1),
            style: TextStyle(
              color: Colors.white,
              fontSize: fontSize,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(width: 2),
          Icon(Icons.star, color: Colors.white, size: iconSize),
        ],
      ),
    );
  }
}

class DiscountBadge extends StatelessWidget {
  final int percentage;

  const DiscountBadge({super.key, required this.percentage});

  @override
  Widget build(BuildContext context) {
    if (percentage <= 0) return const SizedBox.shrink();
    return Text(
      '$percentage% off',
      style: const TextStyle(
        color: AppColors.successGreen,
        fontSize: 12,
        fontWeight: FontWeight.bold,
      ),
    );
  }
}
