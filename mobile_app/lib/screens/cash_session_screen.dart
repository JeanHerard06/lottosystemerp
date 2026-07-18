import 'package:flutter/material.dart';
import '../api_client.dart';

class CashSessionScreen extends StatefulWidget {
  const CashSessionScreen({super.key});
  @override
  State<CashSessionScreen> createState() => _CashSessionScreenState();
}

class _CashSessionScreenState extends State<CashSessionScreen> {
  Map<String, dynamic>? session;
  Map<String, dynamic>? totals;
  bool loading = false;
  final opening = TextEditingController(text: '0');
  final closing = TextEditingController(text: '0');
  final notes = TextEditingController();

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('cash/status.php');
      session = res['session'] as Map<String, dynamic>?;
      totals = res['totals'] as Map<String, dynamic>?;
      if (totals != null && (closing.text == '0' || closing.text.isEmpty)) {
        closing.text = '${totals?['expected_amount'] ?? 0}';
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> openSession() async {
    try {
      final res = await ApiClient().postJson('cash/open.php', {
        'opening_amount': double.tryParse(opening.text) ?? 0,
        'notes': notes.text,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Session ouverte')));
      await load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  double _toDouble(Object? value) => double.tryParse('$value') ?? 0;

  Future<void> closeSession() async {
    final closingAmount = double.tryParse(closing.text) ?? 0;
    final expected = _toDouble(totals?['expected_amount']);
    final difference = closingAmount - expected;

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Résumé fermeture'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Opening: ${session?['opening_amount'] ?? 0}'),
            Text('Ventes: ${totals?['sales'] ?? 0}'),
            Text('Gains payés: ${totals?['paid_gains'] ?? 0}'),
            Text('Dépôts: ${totals?['deposits'] ?? 0}'),
            Text('Retraits: ${totals?['withdrawals'] ?? 0}'),
            const Divider(),
            Text('Cash attendu: ${expected.toStringAsFixed(2)}'),
            Text('Cash réel: ${closingAmount.toStringAsFixed(2)}'),
            Text('Différence: ${difference.toStringAsFixed(2)}', style: TextStyle(fontWeight: FontWeight.bold, color: difference.abs() > 0.01 ? Colors.orange : Colors.green)),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Confirmer')),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiClient().postJson('cash/close.php', {
        'closing_amount': closingAmount,
        'notes': notes.text,
      });
      if (!mounted) return;
      final serverExpected = double.tryParse('${res['expected_amount'] ?? expected}') ?? expected;
      final serverDifference = double.tryParse('${res['difference'] ?? difference}') ?? difference;
      await showDialog(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Session fermée'),
          content: Text('Cash attendu: ${serverExpected.toStringAsFixed(2)} HTG\nDifférence: ${serverDifference.toStringAsFixed(2)} HTG'),
          actions: [FilledButton(onPressed: () => Navigator.pop(context), child: const Text('OK'))],
        ),
      );
      await load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  void initState() { super.initState(); load(); }

  @override
  Widget build(BuildContext context) {
    final isOpen = session != null;
    return Scaffold(
      appBar: AppBar(title: const Text('Cash Session')),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (loading) const LinearProgressIndicator(),
          Card(child: ListTile(
            leading: Icon(isOpen ? Icons.lock_open : Icons.lock),
            title: Text(isOpen ? 'Session ouverte #${session!['id']}' : 'Aucune session ouverte'),
            subtitle: Text(isOpen ? 'Ouverte: ${session!['opened_at']}' : 'Ouvrez une session avant de vendre.'),
          )),
          if (isOpen) ...[
            Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Opening: ${session!['opening_amount']}'),
              Text('Ventes: ${totals?['sales'] ?? 0}'),
              Text('Gains payés: ${totals?['paid_gains'] ?? 0}'),
              Text('Dépôts: ${totals?['deposits'] ?? 0}'),
              Text('Retraits: ${totals?['withdrawals'] ?? 0}'),
              const SizedBox(height: 12),
              TextField(controller: closing, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Montant réel fermeture')),
              const SizedBox(height: 12),
              TextField(controller: notes, decoration: const InputDecoration(labelText: 'Notes')),
              const SizedBox(height: 12),
              FilledButton.icon(onPressed: closeSession, icon: const Icon(Icons.close), label: const Text('Fermer session')),
            ]))),
          ] else ...[
            TextField(controller: opening, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Montant ouverture')),
            const SizedBox(height: 12),
            TextField(controller: notes, decoration: const InputDecoration(labelText: 'Notes')),
            const SizedBox(height: 12),
            FilledButton.icon(onPressed: openSession, icon: const Icon(Icons.lock_open), label: const Text('Ouvrir session')),
          ],
        ]),
      ),
    );
  }
}
