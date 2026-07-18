import 'package:flutter/material.dart';
import '../api_client.dart';

class GainsScreen extends StatefulWidget {
  const GainsScreen({super.key});

  @override
  State<GainsScreen> createState() => _GainsScreenState();
}

class _GainsScreenState extends State<GainsScreen> {
  List entries = [];
  double todayPaid = 0;
  bool loading = false;

  @override
  void initState() {
    super.initState();
    load();
  }

  double money(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse('${value ?? 0}') ?? 0.0;
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('gains/history.php');
      final data = res['data'] as Map<String, dynamic>? ?? {};
      if (!mounted) return;
      setState(() {
        todayPaid = money(data['today_paid_gains']);
        entries = data['entries'] as List? ?? [];
      });
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
      appBar: AppBar(title: const Text('Gains'), actions: [IconButton(onPressed: load, icon: const Icon(Icons.refresh))]),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (loading) const LinearProgressIndicator(),
          Card(
            child: ListTile(
              leading: const Icon(Icons.emoji_events),
              title: const Text('Gains payés aujourd’hui'),
              trailing: Text('${todayPaid.toStringAsFixed(2)} HTG', style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 12),
          Text('Historique paiements gains', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          if (entries.isEmpty && !loading) const Card(child: ListTile(title: Text('Aucun gain payé.'))),
          for (final e in entries)
            Card(
              child: ListTile(
                leading: const Icon(Icons.payments),
                title: Text('${money(e['amount']).toStringAsFixed(2)} HTG'),
                subtitle: Text('${e['description'] ?? ''}\n${e['created_at'] ?? ''}'),
                isThreeLine: true,
              ),
            ),
        ]),
      ),
    );
  }
}
