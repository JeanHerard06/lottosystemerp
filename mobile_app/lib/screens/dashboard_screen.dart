import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_client.dart';
import 'login_screen.dart';
import 'new_fiche_screen.dart';
import 'fiches_screen.dart';
import 'tirages_screen.dart';
import 'balance_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? data;

  Future<void> load() async {
    final res = await ApiClient().get('dashboard.php');
    setState(() => data = res['data']);
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    if (!mounted) return;
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  void initState() {
    super.initState();
    load();
  }

  Widget card(String title, Object? value) {
    return Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title), const SizedBox(height: 8), Text('${value ?? 0}', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold))])));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Dashboard Agent'), actions: [IconButton(onPressed: logout, icon: const Icon(Icons.logout))]),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              children: [
                card('Fiches jodi a', data?['today_fiches']),
                card('Ventes', data?['today_sales']),
                card('Gains', data?['today_gains']),
                card('Balance', data?['balance']),
              ],
            ),
            const SizedBox(height: 16),
            FilledButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewFicheScreen())), child: const Text('+ Nouvelle fiche')),
            OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const FichesScreen())), child: const Text('Mes fiches')),
            OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const TiragesScreen())), child: const Text('Tirages')),
            OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BalanceScreen())), child: const Text('Balance')),
          ],
        ),
      ),
    );
  }
}
