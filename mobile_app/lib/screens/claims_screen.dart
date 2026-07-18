import 'package:flutter/material.dart';
import '../api_client.dart';

class ClaimsScreen extends StatefulWidget {
  const ClaimsScreen({super.key, this.initialCode});
  final String? initialCode;

  @override
  State<ClaimsScreen> createState() => _ClaimsScreenState();
}

class _ClaimsScreenState extends State<ClaimsScreen> {
  final code = TextEditingController();
  final notes = TextEditingController();
  List claims = [];
  bool loading = false;

  @override
  void initState() {
    super.initState();
    code.text = widget.initialCode ?? '';
    load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('claims_list.php');
      claims = res['data'] ?? [];
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> submit() async {
    if (code.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Antre kòd ticket la.')));
      return;
    }
    setState(() => loading = true);
    try {
      final res = await ApiClient().postJson('claims_store.php', {
        'code': code.text.trim(),
        'notes': notes.text.trim(),
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Claim soumise')));
      code.clear();
      notes.clear();
      await load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Color statusColor(String status) {
    switch (status) {
      case 'approved':
      case 'paid':
        return Colors.green;
      case 'rejected':
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Claims gains')),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (loading) const LinearProgressIndicator(),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(children: [
                TextField(controller: code, decoration: const InputDecoration(labelText: 'Code ticket / fiche')),
                const SizedBox(height: 12),
                TextField(controller: notes, decoration: const InputDecoration(labelText: 'Notes'), maxLines: 2),
                const SizedBox(height: 12),
                SizedBox(width: double.infinity, child: FilledButton.icon(onPressed: loading ? null : submit, icon: const Icon(Icons.send), label: const Text('Soumettre claim'))),
              ]),
            ),
          ),
          const SizedBox(height: 12),
          const Text('Mes dernières claims', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          for (final c in claims)
            Card(child: ListTile(
              leading: Icon(Icons.assignment_turned_in, color: statusColor('${c['status']}')),
              title: Text('${c['fiche_code'] ?? 'Ticket'} • ${c['amount'] ?? 0} HTG'),
              subtitle: Text('${c['status']} • ${c['created_at'] ?? ''}\n${c['notes'] ?? ''}'),
            )),
          if (claims.isEmpty && !loading) const Padding(padding: EdgeInsets.all(16), child: Text('Aucune claim pour le moment.')),
        ]),
      ),
    );
  }
}
