import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_client.dart';
import 'dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final username = TextEditingController();
  final password = TextEditingController();
  bool loading = false;

  Future<void> login() async {
    setState(() => loading = true);
    final data = await ApiClient().postForm('login.php', {
      'username': username.text.trim(),
      'password': password.text,
      'device_id': 'flutter-device',
    });
    setState(() => loading = false);

    if (data['success'] == true) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('mobile_token', data['token']);
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const DashboardScreen()));
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? 'Erreur')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('MCS Lotto Agent', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold)),
              const SizedBox(height: 24),
              TextField(controller: username, decoration: const InputDecoration(labelText: 'Identifiant')),
              const SizedBox(height: 12),
              TextField(controller: password, obscureText: true, decoration: const InputDecoration(labelText: 'Mot de passe')),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: FilledButton(onPressed: loading ? null : login, child: Text(loading ? 'Connexion...' : 'Se connecter')),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
