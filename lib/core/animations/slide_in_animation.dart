import 'package:flutter/material.dart';

enum SlideDirection { fromLeft, fromRight, fromTop, fromBottom }

/// Slide in animation widget
class SlideInAnimation extends StatefulWidget {
  final Widget child;
  final Duration duration;
  final Curve curve;
  final SlideDirection direction;
  final double offset;
  final VoidCallback? onComplete;

  const SlideInAnimation({
    super.key,
    required this.child,
    this.duration = const Duration(milliseconds: 500),
    this.curve = Curves.easeInOut,
    this.direction = SlideDirection.fromLeft,
    this.offset = 50.0,
    this.onComplete,
  });

  @override
  State<SlideInAnimation> createState() => _SlideInAnimationState();
}

class _SlideInAnimationState extends State<SlideInAnimation> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(duration: widget.duration, vsync: this);

    Offset beginOffset;
    switch (widget.direction) {
      case SlideDirection.fromLeft:
        beginOffset = Offset(-widget.offset / 100, 0);
        break;
      case SlideDirection.fromRight:
        beginOffset = Offset(widget.offset / 100, 0);
        break;
      case SlideDirection.fromTop:
        beginOffset = Offset(0, -widget.offset / 100);
        break;
      case SlideDirection.fromBottom:
        beginOffset = Offset(0, widget.offset / 100);
        break;
    }

    _slideAnimation = Tween<Offset>(begin: beginOffset, end: Offset.zero).animate(
      CurvedAnimation(parent: _controller, curve: widget.curve),
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: widget.curve),
    );

    _controller.forward().then((_) => widget.onComplete?.call());
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _fadeAnimation,
      child: SlideTransition(position: _slideAnimation, child: widget.child),
    );
  }
}
