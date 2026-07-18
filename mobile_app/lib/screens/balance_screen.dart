import 'package:flutter/material.dart';
import '../api_client.dart';

class BalanceScreen extends StatefulWidget {
  const BalanceScreen({super.key});
  @override
  State<BalanceScreen> createState() => _BalanceScreenState();
}

class _BalanceScreenState extends State<BalanceScreen> {
  Map<String, dynamic>? payload;
  bool loading = false;

  double money(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse('${value ?? 0}') ?? 0.0;
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('balance.php');
      if (!mounted) return;
      setState(() => payload = (res['data'] as Map<String, dynamic>?) ?? res);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  void initState() {
    super.initState();
    load();
  }

  Widget amountCard(String title, Object? value, IconData icon, {String? subtitle}) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [Icon(icon), const SizedBox(width: 8), Expanded(child: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)))]),
            const SizedBox(height: 12),
            Text('${money(value).toStringAsFixed(2)} HTG', style: const TextStyle(fontSize: 25, fontWeight: FontWeight.bold)),
            if (subtitle != null) ...[
              const SizedBox(height: 6),
              Text(subtitle, style: Theme.of(context).textTheme.bodySmall),
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final tx = payload?['transactions'] as List? ?? [];
    final components = payload?['components'] as Map<String, dynamic>? ?? {};
    final hasSession = payload?['has_open_session'] == true;

    return Scaffold(
      appBar: AppBar(title: const Text('Situation financière')),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (loading) const LinearProgressIndicator(),
            amountCard(
              'Encaisse attendue',
              payload?['cash_on_hand'],
              Icons.account_balance_wallet,
              subtitle: hasSession ? 'Calculée sur la session de caisse ouverte.' : 'Aucune session ouverte: position historique.',
            ),
            amountCard(
              'Commission acquise',
              payload?['commission_earned'],
              Icons.percent,
              subtitle: 'Montant gagné selon les règles de commission.',
            ),
            amountCard(
              'À remettre',
              payload?['amount_to_remit'] ?? payload?['balance'],
              Icons.account_balance,
              subtitle: 'Ventes + dépôts - gains payés - retraits - commission.',
            ),
            const SizedBox(height: 12),
            const Text('Détail du calcul', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            Card(
              child: Column(children: [
                ListTile(title: const Text('Ouverture caisse'), trailing: Text('${money(components['opening_cash']).toStringAsFixed(2)} HTG')),
                ListTile(title: const Text('Ventes'), trailing: Text('${money(components['sales']).toStringAsFixed(2)} HTG')),
                ListTile(title: const Text('Dépôts'), trailing: Text('${money(components['deposits']).toStringAsFixed(2)} HTG')),
                ListTile(title: const Text('Gains payés'), trailing: Text('- ${money(components['gains_paid']).toStringAsFixed(2)} HTG')),
                ListTile(title: const Text('Retraits'), trailing: Text('- ${money(components['withdrawals']).toStringAsFixed(2)} HTG')),
                ListTile(title: const Text('Commission retenue'), trailing: Text('- ${money(components['commission']).toStringAsFixed(2)} HTG')),
              ]),
            ),
            const SizedBox(height: 16),
            const Text('Dernières transactions', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            if (tx.isEmpty)
              const Card(child: ListTile(title: Text('Aucune transaction récente.'))),
            for (final t in tx)
              Card(
                child: ListTile(
                  title: Text('${t['type']} - ${money(t['amount']).toStringAsFixed(2)} HTG'),
                  subtitle: Text('${t['description'] ?? ''}\n${t['created_at'] ?? ''}'),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
