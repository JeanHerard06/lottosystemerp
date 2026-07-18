import 'package:flutter/material.dart';
import '../api_client.dart';
import '../core/storage/local_database.dart';
import '../features/sync/sync_queue_service.dart';
import 'fiche_detail_screen.dart';

class FichesScreen extends StatefulWidget {
  const FichesScreen({super.key});
  @override
  State<FichesScreen> createState() => _FichesScreenState();
}

class _FichesScreenState extends State<FichesScreen> {
  List serverItems = [];
  List localItems = [];
  List syncLogs = [];
  bool loading = false;
  bool syncing = false;

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('fiches/list.php');
      serverItems = res['data'] ?? [];
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
    localItems = await LocalDatabase.instance.recentLocalTickets();
    syncLogs = await LocalDatabase.instance.recentSyncLogs(limit: 20);
    if (mounted) setState(() => loading = false);
  }

  Future<void> sync() async {
    setState(() => syncing = true);
    try {
      final count = await SyncQueueService().syncPendingTickets();
      await load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$count ticket(s) synchronisé(s).')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => syncing = false);
    }
  }

  @override
  void initState() { super.initState(); load(); }

  Color statusColor(String status) {
    switch (status) {
      case 'synced':
        return Colors.green;
      case 'failed':
        return Colors.red;
      case 'pending_sync':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  Widget syncChip(String status) {
    final color = statusColor(status);
    return Chip(
      avatar: Icon(status == 'synced' ? Icons.cloud_done : status == 'failed' ? Icons.error_outline : Icons.sync, size: 18, color: color),
      label: Text(status),
      side: BorderSide(color: color.withOpacity(.4)),
    );
  }

  @override
  Widget build(BuildContext context){
    final pending = localItems.where((f) => f['sync_status'] == 'pending_sync' || f['sync_status'] == 'failed').length;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes fiches'),
        actions: [
          IconButton(onPressed: syncing ? null : sync, icon: syncing ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.sync)),
          IconButton(onPressed: load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(padding: const EdgeInsets.all(8), children: [
          if (loading) const LinearProgressIndicator(),
          Card(
            child: ListTile(
              leading: Icon(pending == 0 ? Icons.cloud_done : Icons.cloud_upload, color: pending == 0 ? Colors.green : Colors.orange),
              title: Text(pending == 0 ? 'Tout est synchronisé' : '$pending ticket(s) à synchroniser'),
              subtitle: const Text('Utilisez Sync now après retour connexion.'),
              trailing: FilledButton.icon(onPressed: syncing ? null : sync, icon: const Icon(Icons.sync), label: const Text('Sync now')),
            ),
          ),
          if (localItems.isNotEmpty) const Padding(padding: EdgeInsets.all(8), child: Text('Tickets locaux / sync', style: TextStyle(fontWeight: FontWeight.bold))),
          for (final f in localItems)
            Card(child: ListTile(
              leading: Icon(f['sync_status'] == 'synced' ? Icons.cloud_done : f['sync_status'] == 'failed' ? Icons.error_outline : Icons.cloud_off, color: statusColor('${f['sync_status']}')),
              title: Text(f['fiche_code']?.toString().isNotEmpty == true ? f['fiche_code'].toString() : f['local_uuid'].toString()),
              subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Wrap(spacing: 6, children: [syncChip('${f['sync_status']}')]),
                if ((f['error_message'] ?? '').toString().isNotEmpty) Text('${f['error_message']}', maxLines: 2, overflow: TextOverflow.ellipsis),
              ]),
              trailing: Text('${f['total_amount']}'),
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => FicheDetailScreen(localFiche: Map<String, dynamic>.from(f)))).then((_) => load()),
            )),
          const Padding(padding: EdgeInsets.all(8), child: Text('Tickets serveur', style: TextStyle(fontWeight: FontWeight.bold))),
          if (serverItems.isEmpty && !loading) const Card(child: ListTile(title: Text('Aucune fiche serveur'))),
          for (final f in serverItems)
            Card(child: ListTile(
              leading: const Icon(Icons.receipt_long),
              title: Text('${f['fiche_code']}'),
              subtitle: Text('${f['status']} • ${f['lottery_name'] ?? '-'} • ${f['created_at']}'),
              trailing: Text('${f['total_amount']}'),
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => FicheDetailScreen(serverFiche: Map<String, dynamic>.from(f)))).then((_) => load()),
            )),
          if (syncLogs.isNotEmpty) const Padding(padding: EdgeInsets.all(8), child: Text('Derniers logs sync', style: TextStyle(fontWeight: FontWeight.bold))),
          for (final log in syncLogs.take(10))
            Card(child: ListTile(
              dense: true,
              leading: Icon(log['status'] == 'synced' ? Icons.check_circle : Icons.error_outline, color: statusColor('${log['status']}')),
              title: Text('${log['status']} • ${log['entity_type']}'),
              subtitle: Text('${log['message']}'),
            )),
        ]),
      ),
    );
  }
}
