import 'package:flutter/material.dart';

/// Common animation durations and curves
class AnimationPresets {
  // Durations
  static const Duration fast = Duration(milliseconds: 200);
  static const Duration normal = Duration(milliseconds: 300);
  static const Duration slow = Duration(milliseconds: 500);
  static const Duration verySlow = Duration(milliseconds: 1000);

  // Page transition curves
  static const Curve pageTransition = Curves.easeInOut;
  static const Curve buttonPress = Curves.easeInOut;
  static const Curve cardEnter = Curves.easeOut;
  static const Curve counterAnimation = Curves.easeInOut;

  // Route page transition builders
  static PageTransitionsBuilder fadePageTransition = FadeTransitionsBuilder();
  static PageTransitionsBuilder slidePageTransition = SlideTransitionsBuilder();
}

/// Custom Fade page transition builder
class FadeTransitionsBuilder extends PageTransitionsBuilder {
  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    return FadeTransition(
      opacity: animation,
      child: child,
    );
  }
}

/// Custom Slide page transition builder
class SlideTransitionsBuilder extends PageTransitionsBuilder {
  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    const begin = Offset(1.0, 0.0);
    const end = Offset.zero;
    const curve = Curves.easeInOut;
    final tween = Tween(begin: begin, end: end).chain(CurveTween(curve: curve));
    return SlideTransition(
      position: animation.drive(tween),
      child: child,
    );
  }
}
