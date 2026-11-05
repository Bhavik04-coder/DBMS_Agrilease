import 'package:flutter/material.dart';

/// Animated counter that counts from 0 to a final value
class AnimatedCounter extends StatefulWidget {
  final int endValue;
  final Duration duration;
  final TextStyle textStyle;
  final String prefix;
  final String suffix;

  const AnimatedCounter({
    super.key,
    required this.endValue,
    this.duration = const Duration(milliseconds: 1500),
    this.textStyle = const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
    this.prefix = '',
    this.suffix = '',
  });

  @override
  State<AnimatedCounter> createState() => _AnimatedCounterState();
}

class _AnimatedCounterState extends State<AnimatedCounter> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<int> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(duration: widget.duration, vsync: this);
    _animation = IntTween(begin: 0, end: widget.endValue).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (context, child) {
        return Text(
          '${widget.prefix}${_animation.value}${widget.suffix}',
          style: widget.textStyle,
        );
      },
    );
  }
}
