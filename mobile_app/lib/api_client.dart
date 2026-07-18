import 'dart:async';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'config.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  ApiException(this.message, [this.statusCode]);
  @override
  String toString() => message;
}

class ApiClient {
  static const Duration timeout = Duration(seconds: 20);

  Future<String?> token() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('mobile_token');
  }

  Uri _uri(String path) {
    final clean = path.startsWith('/') ? path.substring(1) : path;
    return Uri.parse('$apiBaseUrl/$clean');
  }

  Future<Map<String, String>> _headers({bool json = false}) async {
    final t = await token();
    return {
      'Accept': 'application/json',
      if (json) 'Content-Type': 'application/json',
      if (t != null && t.isNotEmpty) 'Authorization': 'Bearer $t',
    };
  }

  Map<String, dynamic> _decode(http.Response response) {
    try {
      final raw = response.body.trim().replaceFirst(RegExp(r'^\uFEFF'), '');
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) {
        if (response.statusCode >= 400 && decoded['success'] != true) {
          throw ApiException(decoded['message']?.toString() ?? 'Erreur API', response.statusCode);
        }
        return decoded;
      }
    } catch (e) {
      if (e is ApiException) rethrow;
    }
    final body = response.body.trim();
    final preview = body.length > 180 ? '${body.substring(0, 180)}...' : body;
    throw ApiException('Réponse API invalide (${response.statusCode})${preview.isNotEmpty ? ': $preview' : ''}', response.statusCode);
  }

  Future<Map<String, dynamic>> postForm(String path, Map<String, String> body) async {
    try {
      final response = await http.post(_uri(path), headers: await _headers(), body: body).timeout(timeout);
      return _decode(response);
    } on TimeoutException {
      throw ApiException('Connexion trop lente. Réessayez.');
    } on http.ClientException {
      throw ApiException('Impossible de joindre le serveur.');
    }
  }

  Future<Map<String, dynamic>> postJson(String path, Map<String, dynamic> body) async {
    try {
      final response = await http
          .post(_uri(path), headers: await _headers(json: true), body: jsonEncode(body))
          .timeout(timeout);
      return _decode(response);
    } on TimeoutException {
      throw ApiException('Connexion trop lente. Ticket gardé localement si possible.');
    } on http.ClientException {
      throw ApiException('Serveur inaccessible. Ticket gardé localement si possible.');
    }
  }

  Future<Map<String, dynamic>> get(String path) async {
    try {
      final response = await http.get(_uri(path), headers: await _headers()).timeout(timeout);
      return _decode(response);
    } on TimeoutException {
      throw ApiException('Connexion trop lente.');
    } on http.ClientException {
      throw ApiException('Impossible de joindre le serveur.');
    }
  }
}
