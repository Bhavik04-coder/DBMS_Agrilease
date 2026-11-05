import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:get_storage/get_storage.dart';
import 'core/theme/app_theme.dart';
import 'core/theme/app_theme_dark.dart';
import 'core/theme/theme_manager.dart';
import 'core/animations/animation_presets.dart';
import 'providers/auth_provider.dart';
import 'providers/products_provider.dart';
import 'providers/bookings_provider.dart';
import 'providers/notifications_provider.dart';
import 'providers/favorites_provider.dart';
import 'ui/features/auth/login_screen.dart';
import 'ui/features/navigation/main_navigation_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await GetStorage.init();
  
  final themeManager = ThemeManager();
  await themeManager.init();
  
  runApp(AgriLeaseApp(themeManager: themeManager));
}

class AgriLeaseApp extends StatelessWidget {
  final ThemeManager themeManager;

  const AgriLeaseApp({super.key, required this.themeManager});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => ProductsProvider()),
        ChangeNotifierProvider(create: (_) => BookingsProvider()),
        ChangeNotifierProvider(create: (_) => NotificationsProvider()),
        ChangeNotifierProvider(create: (_) => FavoritesProvider()),
        ChangeNotifierProvider(create: (_) => themeManager),
      ],
      child: Consumer2<AuthProvider, ThemeManager>(
        builder: (context, auth, theme, _) {
          return MaterialApp(
            title: 'AgriLease - Farm Machinery Rental',
            debugShowCheckedModeBanner: false,
            themeMode: theme.themeMode,
            theme: AppTheme.lightTheme,
            darkTheme: AppThemeDark.darkTheme,
            builder: (context, child) {
              return PageTransitionsTheme(
                builders: {
                  TargetPlatform.android: AnimationPresets.slidePageTransition,
                  TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
                },
              ) as Widget? ?? child ?? const SizedBox();
            },
            home: auth.isAuthenticated
                ? const MainNavigationScreen()
                : const LoginScreen(),
          );
        },
      ),
    );
  }
}
