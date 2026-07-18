import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_client.dart';
import '../config.dart';
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
    if (username.text.trim().isEmpty || password.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Antre identifiant ak mot de passe.')));
      return;
    }
    setState(() => loading = true);
    try {
      final data = await ApiClient().postForm('login.php', {
        'username': username.text.trim(),
        'password': password.text,
        'device_id': defaultDeviceId,
      });
      if (data['success'] == true) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('mobile_token', data['token'].toString());
        await prefs.setString('mobile_user_name', data['user']?['name']?.toString() ?? 'Agent');
        await prefs.setString('mobile_user_role', data['user']?['role']?.toString() ?? 'agent');
        if (!mounted) return;
        Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const DashboardScreen()));
      } else {
        throw ApiException(data['message']?.toString() ?? 'Connexion refusée');
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Icon(Icons.confirmation_number, size: 72),
                  const SizedBox(height: 12),
                  const Text('Lotto ERP Agent', textAlign: TextAlign.center, style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  const Text('Connexion mobile sécurisée', textAlign: TextAlign.center),
                  const SizedBox(height: 24),
                  TextField(controller: username, decoration: const InputDecoration(labelText: 'Identifiant')),
                  const SizedBox(height: 12),
                  TextField(controller: password, obscureText: true, decoration: const InputDecoration(labelText: 'Mot de passe')),
                  const SizedBox(height: 24),
                  FilledButton.icon(
                    onPressed: loading ? null : login,
                    icon: loading ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.login),
                    label: Text(loading ? 'Connexion...' : 'Se connecter'),
                  ),
                  const SizedBox(height: 16),
                  Text('API: $apiBaseUrl', textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodySmall),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
