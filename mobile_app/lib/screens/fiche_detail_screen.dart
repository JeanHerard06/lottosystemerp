import 'dart:convert';
import 'package:flutter/material.dart';
import '../api_client.dart';
import '../features/printing/bluetooth_print_service.dart';
import '../widgets/mobile_ticket_view.dart';

class FicheDetailScreen extends StatefulWidget {
  final Map<String, dynamic>? serverFiche;
  final Map<String, dynamic>? localFiche;

  const FicheDetailScreen({super.key, this.serverFiche, this.localFiche});

  @override
  State<FicheDetailScreen> createState() => _FicheDetailScreenState();
}

class _FicheDetailScreenState extends State<FicheDetailScreen> {
  Map<String, dynamic>? fiche;
  List details = [];
  bool loading = false;

  bool get isLocal => widget.localFiche != null;

  @override
  void initState() {
    super.initState();
    load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      if (isLocal) {
        final local = Map<String, dynamic>.from(widget.localFiche!);
        final payload = jsonDecode(local['payload'].toString()) as Map<String, dynamic>;
        final plays = payload['plays'] as List? ?? [];
        fiche = {
          'local_uuid': local['local_uuid'],
          'fiche_code': local['fiche_code'] ?? '',
          'total_amount': local['total_amount'],
          'status': local['sync_status'],
          'created_at': local['created_at'],
          'lottery_name': 'Ticket local',
        };
        details = plays.map((p) => {
          'play_type': p['type'],
          'number_played': p['number'],
          'amount': p['amount'],
        }).toList();
      } else {
        final server = widget.serverFiche ?? {};
        final id = int.tryParse('${server['id'] ?? 0}') ?? 0;
        final code = Uri.encodeComponent('${server['fiche_code'] ?? server['code'] ?? ''}'.trim());
        final localUuid = Uri.encodeComponent('${server['local_uuid'] ?? ''}'.trim());
        final parts = <String>[];
        if (id > 0) parts.add('id=$id');
        if (code.isNotEmpty) parts.add('code=$code');
        if (localUuid.isNotEmpty) parts.add('local_uuid=$localUuid');
        final query = parts.isEmpty ? '' : '?${parts.join('&')}';
        final res = await ApiClient().get('fiches/show.php$query');
        fiche = Map<String, dynamic>.from(res['fiche'] ?? res['data']?['fiche'] ?? {});
        details = List.from(res['details'] ?? res['data']?['details'] ?? []);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> previewPrint() async {
    final f = fiche;
    if (f == null) return;
    await showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Aperçu ticket'),
        content: SingleChildScrollView(child: MobileTicketView(fiche: f, details: details)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Fermer')),
          FilledButton.icon(
            onPressed: () async {
              Navigator.pop(context);
              await BluetoothPrintService().printTicket80mm({'fiche': f, 'details': details});
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Commande impression envoyée.')));
              }
            },
            icon: const Icon(Icons.print),
            label: const Text('Imprimer'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final f = fiche;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Détail fiche'),
        actions: [
          IconButton(onPressed: loading ? null : load, icon: const Icon(Icons.refresh)),
          IconButton(onPressed: f == null ? null : previewPrint, icon: const Icon(Icons.print)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (loading) const LinearProgressIndicator(),
            if (f == null && !loading) const Card(child: ListTile(title: Text('Fiche introuvable'))),
            if (f != null) ...[
              MobileTicketView(fiche: f, details: details),
              const SizedBox(height: 8),
              Card(child: ListTile(
                leading: Icon(isLocal ? Icons.cloud_off : Icons.cloud_done),
                title: Text('Statut: ${f['status'] ?? '-'}'),
                subtitle: Text(isLocal ? 'Ticket local/offline' : 'Ticket serveur'),
              )),
              FilledButton.icon(onPressed: previewPrint, icon: const Icon(Icons.print), label: const Text('Réimprimer')),
            ],
          ],
        ),
      ),
    );
  }
}
