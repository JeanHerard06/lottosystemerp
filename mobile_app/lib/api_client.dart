import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'config.dart';

class ApiClient {
  Future<String?> token() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('mobile_token');
  }

  Future<Map<String, dynamic>> postForm(String path, Map<String, String> body) async {
    final response = await http.post(Uri.parse('$apiBaseUrl/$path'), body: body);
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> postJson(String path, Map<String, dynamic> body) async {
    final t = await token();
    final response = await http.post(
      Uri.parse('$apiBaseUrl/$path'),
      headers: {
        'Content-Type': 'application/json',
        if (t != null) 'Authorization': 'Bearer $t',
      },
      body: jsonEncode(body),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> get(String path) async {
    final t = await token();
    final response = await http.get(
      Uri.parse('$apiBaseUrl/$path'),
      headers: {if (t != null) 'Authorization': 'Bearer $t'},
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}
