import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config.dart';
import '../models/user.dart';

class AuthService extends ChangeNotifier {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  User? _currentUser;
  String? _token;
  bool _isLoading = false;

  User? get currentUser => _currentUser;
  String? get token => _token;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _token != null && _currentUser != null;

  // Initialiser depuis le stockage local
  Future<bool> init() async {
    _isLoading = true;
    notifyListeners();

    try {
      final prefs = await SharedPreferences.getInstance();
      _token = prefs.getString(Config.tokenKey);
      final userJson = prefs.getString(Config.userKey);

      if (userJson != null) {
        _currentUser = User.fromJson(jsonDecode(userJson));
      }

      _isLoading = false;
      notifyListeners();
      return isAuthenticated;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Obtenir le token
  Future<String?> getToken() async {
    if (_token != null) return _token;

    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(Config.tokenKey);
    return _token;
  }

  // Sauvegarder les donnees de connexion
  Future<void> saveLoginData(String token, Map<String, dynamic> userData) async {
    final prefs = await SharedPreferences.getInstance();

    _token = token;
    _currentUser = User.fromJson(userData);

    await prefs.setString(Config.tokenKey, token);
    await prefs.setString(Config.userKey, jsonEncode(userData));

    notifyListeners();
  }

  // Deconnexion
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();

    _token = null;
    _currentUser = null;

    await prefs.remove(Config.tokenKey);
    await prefs.remove(Config.userKey);

    notifyListeners();
  }

  // Mettre a jour l'utilisateur
  void updateUser(User user) {
    _currentUser = user;
    notifyListeners();
  }
}
