import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class SkeletonLoader extends StatelessWidget {
  final double width;
  final double height;
  final BorderRadius borderRadius;

  const SkeletonLoader({
    super.key,
    this.width = double.infinity,
    this.height = 200,
    BorderRadius? borderRadius,
  }) : borderRadius = borderRadius ?? const BorderRadius.all(Radius.circular(8));

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Theme.of(context).brightness == Brightness.dark
          ? Colors.grey[800]!
          : Colors.grey[300]!,
      highlightColor: Theme.of(context).brightness == Brightness.dark
          ? Colors.grey[700]!
          : Colors.grey[100]!,
      child: Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: Colors.grey,
          borderRadius: borderRadius,
        ),
      ),
    );
  }
}

class ProductCardSkeleton extends StatelessWidget {
  const ProductCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Column(
        children: [
          SkeletonLoader(height: 120),
          Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SkeletonLoader(
                  width: double.infinity,
                  height: 14,
                  borderRadius: const BorderRadius.all(Radius.circular(4)),
                ),
                const SizedBox(height: 8),
                SkeletonLoader(
                  width: 80,
                  height: 11,
                  borderRadius: const BorderRadius.all(Radius.circular(4)),
                ),
                const SizedBox(height: 8),
                SkeletonLoader(
                  width: double.infinity,
                  height: 14,
                  borderRadius: const BorderRadius.all(Radius.circular(4)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class BookingCardSkeleton extends StatelessWidget {
  const BookingCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: SkeletonLoader(
                    width: 150,
                    height: 14,
                    borderRadius: const BorderRadius.all(Radius.circular(4)),
                  ),
                ),
                SkeletonLoader(
                  width: 60,
                  height: 12,
                  borderRadius: const BorderRadius.all(Radius.circular(4)),
                ),
              ],
            ),
            const SizedBox(height: 12),
            SkeletonLoader(
              width: double.infinity,
              height: 11,
              borderRadius: const BorderRadius.all(Radius.circular(4)),
            ),
            const SizedBox(height: 8),
            SkeletonLoader(
              width: 120,
              height: 11,
              borderRadius: const BorderRadius.all(Radius.circular(4)),
            ),
          ],
        ),
      ),
    );
  }
}
