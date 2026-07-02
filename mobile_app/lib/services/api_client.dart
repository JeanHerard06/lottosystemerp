import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';

class ApiClient {
  String? token;

  Map<String, String> get headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  Future<Map<String, dynamic>> get(String path) async {
    final res = await http.get(Uri.parse('${ApiConfig.baseUrl}$path'), headers: headers);
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body) async {
    final res = await http.post(Uri.parse('${ApiConfig.baseUrl}$path'), headers: headers, body: jsonEncode(body));
    return jsonDecode(res.body) as Map<String, dynamic>;
  }
}
