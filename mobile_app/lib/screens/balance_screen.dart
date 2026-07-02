import 'package:flutter/material.dart';
import '../api_client.dart';

class BalanceScreen extends StatefulWidget {
  const BalanceScreen({super.key});
  @override
  State<BalanceScreen> createState() => _BalanceScreenState();
}

class _BalanceScreenState extends State<BalanceScreen> {
  Map<String, dynamic>? data;
  Future<void> load() async { final res = await ApiClient().get('balance.php'); setState(() => data = res); }
  @override void initState(){ super.initState(); load(); }
  @override Widget build(BuildContext context){ final tx = data?['transactions'] ?? []; return Scaffold(appBar: AppBar(title: const Text('Balance')), body: ListView(padding: const EdgeInsets.all(16), children: [Text('Balance: ${data?['balance'] ?? 0}', style: const TextStyle(fontSize: 26, fontWeight: FontWeight.bold)), const SizedBox(height: 16), for(final t in tx) ListTile(title: Text('${t['type']} - ${t['amount']}'), subtitle: Text(t['description'] ?? ''))])); }
}
