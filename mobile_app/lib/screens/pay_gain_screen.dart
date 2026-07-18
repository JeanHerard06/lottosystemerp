import 'package:flutter/material.dart';
import '../api_client.dart';

class PayGainScreen extends StatefulWidget {
  const PayGainScreen({super.key, this.initialCode});
  final String? initialCode;

  @override
  State<PayGainScreen> createState() => _PayGainScreenState();
}

class _PayGainScreenState extends State<PayGainScreen> {
  final codeController = TextEditingController();
  Map<String, dynamic>? verification;
  List history = [];
  bool loading = false;
  bool paying = false;

  @override
  void initState() {
    super.initState();
    codeController.text = widget.initialCode ?? '';
    loadHistory();
    if (codeController.text.trim().isNotEmpty) {
      verify();
    }
  }

  @override
  void dispose() {
    codeController.dispose();
    super.dispose();
  }

  double money(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse('${value ?? 0}') ?? 0.0;
  }

  Future<void> loadHistory() async {
    try {
      final res = await ApiClient().get('gains/history.php');
      if (!mounted) return;
      setState(() => history = res['data']?['entries'] ?? []);
    } catch (_) {
      // History is optional and must not block the payment workflow.
    }
  }

  Future<void> verify() async {
    final code = codeController.text.trim();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Antre kòd ticket la.')));
      return;
    }
    setState(() {
      loading = true;
      verification = null;
    });
    try {
      final res = await ApiClient().postJson('gains/verify.php', {'code': code});
      if (!mounted) return;
      setState(() => verification = res);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> pay() async {
    final v = verification;
    if (v == null || v['payable'] != true) return;

    final pending = money(v['summary']?['total_pending']);
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Confirmer paiement'),
        content: Text('Peye gain ${pending.toStringAsFixed(2)} HTG pou ticket ${codeController.text.trim()} ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Payer')),
        ],
      ),
    );
    if (confirm != true) return;

    setState(() => paying = true);
    try {
      final res = await ApiClient().postJson('gains/pay.php', {'code': codeController.text.trim()});
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Gain payé')));
      await verify();
      await loadHistory();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => paying = false);
    }
  }

  Widget verificationCard() {
    final v = verification;
    if (v == null) return const SizedBox.shrink();
    final valid = v['valid'] == true;
    final payable = v['payable'] == true;
    final ticket = v['ticket'] as Map<String, dynamic>?;
    final summary = v['summary'] as Map<String, dynamic>? ?? {};
    final gains = v['gains'] as List? ?? [];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Icon(valid ? Icons.verified : Icons.error_outline, color: valid ? Colors.green : Colors.red),
            const SizedBox(width: 8),
            Expanded(child: Text(v['message']?.toString() ?? '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18))),
          ]),
          if (ticket != null) ...[
            const Divider(),
            Text('Ticket: ${ticket['fiche_code'] ?? '-'}'),
            Text('Lottery: ${ticket['lottery_name'] ?? '-'}'),
            Text('Statut: ${ticket['status'] ?? '-'}'),
            Text('Total ticket: ${money(ticket['total_amount']).toStringAsFixed(2)} HTG'),
          ],
          const SizedBox(height: 12),
          Wrap(spacing: 8, runSpacing: 8, children: [
            Chip(label: Text('Gain total: ${money(summary['total_won']).toStringAsFixed(2)} HTG')),
            Chip(label: Text('Déjà payé: ${money(summary['total_paid']).toStringAsFixed(2)} HTG')),
            Chip(label: Text('À payer: ${money(summary['total_pending']).toStringAsFixed(2)} HTG')),
          ]),
          if (gains.isNotEmpty) ...[
            const SizedBox(height: 12),
            const Text('Lignes gagnantes', style: TextStyle(fontWeight: FontWeight.bold)),
            for (final g in gains)
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Icon((int.tryParse('${g['is_paid'] ?? 0}') ?? 0) == 1 ? Icons.check_circle : Icons.emoji_events, color: (int.tryParse('${g['is_paid'] ?? 0}') ?? 0) == 1 ? Colors.green : Colors.orange),
                title: Text('${g['number_played'] ?? '-'} • ${g['play_type'] ?? '-'}'),
                subtitle: Text('${g['draw_name'] ?? ''} ${g['draw_date'] ?? ''}'),
                trailing: Text('${money(g['amount_won']).toStringAsFixed(2)} HTG'),
              ),
          ],
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: payable && !paying ? pay : null,
              icon: const Icon(Icons.payments),
              label: Text(paying ? 'Paiement...' : 'Payer gain'),
            ),
          ),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Payer gain')),
      body: RefreshIndicator(
        onRefresh: () async {
          if (codeController.text.trim().isNotEmpty) await verify();
          await loadHistory();
        },
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (loading || paying) const LinearProgressIndicator(),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(children: [
                TextField(
                  controller: codeController,
                  decoration: const InputDecoration(labelText: 'Code ticket / fiche'),
                  textInputAction: TextInputAction.search,
                  onSubmitted: (_) => verify(),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: loading ? null : verify,
                    icon: const Icon(Icons.search),
                    label: const Text('Vérifier gain'),
                  ),
                ),
              ]),
            ),
          ),
          const SizedBox(height: 12),
          verificationCard(),
          const SizedBox(height: 16),
          const Text('Derniers gains payés', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          if (history.isEmpty)
            const Card(child: ListTile(title: Text('Aucun paiement récent.'))),
          for (final h in history)
            Card(
              child: ListTile(
                leading: const Icon(Icons.payments),
                title: Text('${money(h['amount']).toStringAsFixed(2)} HTG'),
                subtitle: Text('${h['description'] ?? ''}\n${h['created_at'] ?? ''}'),
              ),
            ),
        ]),
      ),
    );
  }
}
