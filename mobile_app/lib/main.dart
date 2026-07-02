import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';

void main() {
  runApp(const LottoAgentApp());
}

class LottoAgentApp extends StatelessWidget {
  const LottoAgentApp({super.key});

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('mobile_token') != null;
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Lotto Agent',
      theme: ThemeData(useMaterial3: true, colorSchemeSeed: Colors.amber),
      home: FutureBuilder<bool>(
        future: isLoggedIn(),
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return const Scaffold(body: Center(child: CircularProgressIndicator()));
          }
          return snapshot.data! ? const DashboardScreen() : const LoginScreen();
        },
      ),
    );
  }
}
