import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config.dart';
import 'auth_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final AuthService _authService = AuthService();

  // Headers avec authentification
  Future<Map<String, String>> _getHeaders() async {
    final token = await _authService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // GET request
  Future<Map<String, dynamic>> get(String endpoint,
      {Map<String, String>? params}) async {
    try {
      var uri = Uri.parse('${Config.apiUrl}$endpoint');
      if (params != null) {
        uri = uri.replace(queryParameters: params);
      }

      final response = await http.get(
        uri,
        headers: await _getHeaders(),
      );

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  // POST request
  Future<Map<String, dynamic>> post(String endpoint,
      {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('${Config.apiUrl}$endpoint');
      final headers = await _getHeaders();

      print('POST Request: $uri');
      print('Headers: $headers');
      if (body != null) print('Body: ${jsonEncode(body)}');

      final response = await http.post(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      return _handleResponse(response);
    } catch (e) {
      print('POST Error: $e');
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  // PUT request
  Future<Map<String, dynamic>> put(String endpoint,
      {Map<String, dynamic>? body}) async {
    try {
      final response = await http.put(
        Uri.parse('${Config.apiUrl}$endpoint'),
        headers: await _getHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  // DELETE request
  Future<Map<String, dynamic>> delete(String endpoint) async {
    try {
      final response = await http.delete(
        Uri.parse('${Config.apiUrl}$endpoint'),
        headers: await _getHeaders(),
      );

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  // Traitement de la reponse
  Map<String, dynamic> _handleResponse(http.Response response) {
    try {
      final data = jsonDecode(response.body);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return data;
      } else if (response.statusCode == 401) {
        // Token expire
        _authService.logout();
        return {'success': false, 'message': 'Session expiree'};
      } else if (response.statusCode == 422) {
        // Erreur de validation
        final errors = data['errors'] ?? {};
        final firstError =
            errors.isNotEmpty ? errors.values.first[0] : 'Erreur de validation';
        return {'success': false, 'message': firstError};
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur serveur'
        };
      }
    } catch (e) {
      return {'success': false, 'message': 'Erreur de traitement: $e'};
    }
  }

  // ========== ENDPOINTS SPECIFIQUES ==========

  // Connexion
  Future<Map<String, dynamic>> login(String email, String password) async {
    return await post('/auth/login', body: {
      'email': email,
      'password': password,
    });
  }

  // Deconnexion
  Future<Map<String, dynamic>> logout() async {
    final result = await post('/auth/logout');
    await _authService.logout();
    return result;
  }

  // Profil utilisateur
  Future<Map<String, dynamic>> getProfile() async {
    return await get('/auth/me');
  }

  // Scanner un QR code (pour employe)
  Future<Map<String, dynamic>> scanQrCode(String qrCode) async {
    return await post('/pointages', body: {
      'qr_code': qrCode,
    });
  }

  // Generer un QR code (pour gardien)
  Future<Map<String, dynamic>> generateQrCode() async {
    return await post('/qrcode/generate');
  }

  // Obtenir le QR code actif
  Future<Map<String, dynamic>> getActiveQrCode() async {
    return await get('/qrcode/current');
  }

  // Historique des pointages
  Future<Map<String, dynamic>> getPointages({
    String? dateDebut,
    String? dateFin,
    int? userId,
  }) async {
    final params = <String, String>{};
    if (dateDebut != null) params['date_debut'] = dateDebut;
    if (dateFin != null) params['date_fin'] = dateFin;
    if (userId != null) params['user_id'] = userId.toString();

    return await get('/pointages', params: params);
  }

  // Mes pointages (pour employe connecte)
  Future<Map<String, dynamic>> getMyPointages({
    String? dateDebut,
    String? dateFin,
  }) async {
    final params = <String, String>{};
    if (dateDebut != null) params['date_debut'] = dateDebut;
    if (dateFin != null) params['date_fin'] = dateFin;

    return await get('/pointages/me', params: params);
  }

  // Mes sessions de travail
  Future<Map<String, dynamic>> getMySessions({
    String? dateDebut,
    String? dateFin,
  }) async {
    final params = <String, String>{};
    if (dateDebut != null) params['date_debut'] = dateDebut;
    if (dateFin != null) params['date_fin'] = dateFin;

    return await get('/pointages/sessions', params: params);
  }

  // Dashboard stats
  Future<Map<String, dynamic>> getDashboard() async {
    return await get('/stats/dashboard');
  }
}
