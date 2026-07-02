import 'package:flutter/material.dart';
import 'features/auth/login_page.dart';

void main() {
  runApp(const LottoErpMobileApp());
}

class LottoErpMobileApp extends StatelessWidget {
  const LottoErpMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Lotto ERP Mobile',
      theme: ThemeData(useMaterial3: true),
      home: const LoginPage(),
    );
  }
}
