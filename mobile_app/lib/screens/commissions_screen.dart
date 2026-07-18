import 'package:flutter/material.dart';
import '../api_client.dart';

class CommissionsScreen extends StatefulWidget {
  const CommissionsScreen({super.key});

  @override
  State<CommissionsScreen> createState() => _CommissionsScreenState();
}

class _CommissionsScreenState extends State<CommissionsScreen> {
  Map<String, dynamic>? dashboard;
  Map<String, dynamic>? history;
  String period = 'month';
  bool loading = false;

  @override
  void initState() {
    super.initState();
    load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final dash = await ApiClient().get('commissions/dashboard.php');
      final hist = await ApiClient().get('commissions/history.php?period=$period');
      if (!mounted) return;
      setState(() {
        dashboard = dash['data'] as Map<String, dynamic>?;
        history = hist['data'] as Map<String, dynamic>?;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  double money(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse('${value ?? 0}') ?? 0.0;
  }

  Widget kpi(String title, Object? value, IconData icon, {String suffix = ' HTG'}) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [Icon(icon, size: 20), const SizedBox(width: 8), Expanded(child: Text(title))]),
          const SizedBox(height: 10),
          Text('${money(value).toStringAsFixed(2)}$suffix', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.bold)),
        ]),
      ),
    );
  }

  Future<void> showRequestDialog() async {
    final amountController = TextEditingController(text: money(dashboard?['month_commission']).toStringAsFixed(2));
    final noteController = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Demande paiement commission'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(
            controller: amountController,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(labelText: 'Montant'),
          ),
          const SizedBox(height: 10),
          TextField(controller: noteController, decoration: const InputDecoration(labelText: 'Note')),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Envoyer')),
        ],
      ),
    );
    if (ok != true) return;

    try {
      final res = await ApiClient().postJson('commissions/request.php', {
        'amount': double.tryParse(amountController.text.replaceAll(',', '.')) ?? 0,
        'note': noteController.text.trim(),
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Demande envoyée')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final transactions = (history?['transactions'] as List?) ?? [];
    final days = (history?['days'] as List?) ?? [];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Commissions'),
        actions: [IconButton(onPressed: load, icon: const Icon(Icons.refresh))],
      ),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (loading) const LinearProgressIndicator(),
          GridView.count(
            crossAxisCount: MediaQuery.of(context).size.width > 650 ? 3 : 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            childAspectRatio: 1.15,
            children: [
              kpi('Commission jodi a', dashboard?['today_commission'], Icons.today),
              kpi('Ventes jodi a', dashboard?['today_sales'], Icons.payments),
              kpi('Commission semèn', dashboard?['week_commission'], Icons.date_range),
              kpi('Commission mwa', dashboard?['month_commission'], Icons.calendar_month),
            ],
          ),
          const SizedBox(height: 8),
          FilledButton.icon(onPressed: showRequestDialog, icon: const Icon(Icons.request_quote), label: const Text('Demander paiement')),
          const SizedBox(height: 16),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'today', label: Text('Jour')),
              ButtonSegment(value: 'week', label: Text('Semaine')),
              ButtonSegment(value: 'month', label: Text('Mois')),
            ],
            selected: {period},
            onSelectionChanged: (s) {
              setState(() => period = s.first);
              load();
            },
          ),
          const SizedBox(height: 16),
          Text('Résumé par jour', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          if (days.isEmpty)
            const Card(child: ListTile(leading: Icon(Icons.info_outline), title: Text('Aucune commission pour cette période.')))
          else
            for (final d in days)
              Card(
                child: ListTile(
                  leading: const Icon(Icons.calendar_today),
                  title: Text('${d['day']}'),
                  subtitle: Text('${d['entries']} entrée(s)'),
                  trailing: Text('${money(d['commission']).toStringAsFixed(2)} HTG', style: const TextStyle(fontWeight: FontWeight.bold)),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CommissionDetailScreen(date: '${d['day']}'))),
                ),
              ),
          const SizedBox(height: 16),
          Text('Dernières commissions', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          for (final t in transactions.take(20))
            Card(
              child: ListTile(
                leading: const Icon(Icons.attach_money),
                title: Text('${money(t['amount']).toStringAsFixed(2)} HTG'),
                subtitle: Text('${t['description'] ?? ''}\n${t['created_at'] ?? ''}'),
                isThreeLine: true,
              ),
            ),
        ]),
      ),
    );
  }
}

class CommissionDetailScreen extends StatefulWidget {
  final String date;
  const CommissionDetailScreen({super.key, required this.date});

  @override
  State<CommissionDetailScreen> createState() => _CommissionDetailScreenState();
}

class _CommissionDetailScreenState extends State<CommissionDetailScreen> {
  Map<String, dynamic>? data;
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
      final res = await ApiClient().get('commissions/details.php?date=${widget.date}');
      if (!mounted) return;
      setState(() => data = res['data'] as Map<String, dynamic>?);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final byGame = (data?['by_game'] as List?) ?? [];
    final entries = (data?['entries'] as List?) ?? [];
    return Scaffold(
      appBar: AppBar(title: Text('Détail ${widget.date}')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (loading) const LinearProgressIndicator(),
        Card(
          child: ListTile(
            title: const Text('Total commission'),
            subtitle: Text('Ventes: ${money(data?['sales']).toStringAsFixed(2)} HTG'),
            trailing: Text('${money(data?['commission']).toStringAsFixed(2)} HTG', style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
        ),
        const SizedBox(height: 12),
        Text('Ventes par jeu', style: Theme.of(context).textTheme.titleMedium),
        for (final g in byGame)
          Card(
            child: ListTile(
              title: Text('${g['play_type']}'),
              subtitle: Text('${g['play_lines'] ?? g['lines'] ?? 0} ligne(s)'),
              trailing: Text('${money(g['sales_amount']).toStringAsFixed(2)} HTG'),
            ),
          ),
        const SizedBox(height: 12),
        Text('Écritures commission', style: Theme.of(context).textTheme.titleMedium),
        for (final e in entries)
          Card(
            child: ListTile(
              title: Text('${money(e['amount']).toStringAsFixed(2)} HTG'),
              subtitle: Text('${e['description'] ?? ''}\n${e['created_at'] ?? ''}'),
              isThreeLine: true,
            ),
          ),
      ]),
    );
  }
}
