/// Application configuration - Change these based on your environment
class AppConfig {
  // Backend Configuration
  static const String backendMode = 'mock'; // 'mock' or 'api'
  
  // API Endpoints
  static const String localApiUrl = 'http://localhost/DBMS_Agrilease/api';
  static const String localMobileApiUrl = 'http://192.168.1.100/DBMS_Agrilease/api'; // For local network
  static const String productionApiUrl = 'https://api.agrilease.com'; // Future production URL
  
  // Current Active URL (Change this to switch environments)
  static String get apiBaseUrl {
    if (backendMode == 'mock') {
      return localApiUrl; // Won't be used with mock mode
    }
    return localApiUrl; // Change to localMobileApiUrl or productionApiUrl as needed
  }
  
  // Feature Flags
  static const bool enableMockData = true; // Set to false to use real API
  static const bool enableLogging = true;
  static const bool enableAnalytics = false;
  
  // Timeouts
  static const Duration apiTimeout = Duration(seconds: 30);
  static const Duration imageTimeout = Duration(seconds: 15);
  
  // Environment Detection
  static bool get isProduction => !isDebug && !isLocalhost;
  static bool get isDebug => true; // Change based on build type
  static bool get isLocalhost => apiBaseUrl.contains('localhost') || apiBaseUrl.contains('192.168');
  
  // Database Configuration
  static const String dbName = 'agrilease_v2';
  static const String dbHost = 'localhost';
  static const String dbUser = 'root';
  static const String dbPassword = '';
  
  // App Metadata
  static const String appName = 'AgriLease';
  static const String appVersion = '1.1.0';
  static const String appBuildNumber = '2';
}
