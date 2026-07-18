import 'dart:convert';
import '../../api_client.dart';
import '../../core/storage/local_database.dart';

class SyncQueueService {
  final ApiClient api;
  SyncQueueService({ApiClient? apiClient}) : api = apiClient ?? ApiClient();

  Future<int> syncPendingTickets() async {
    final rows = await LocalDatabase.instance.pendingTickets();
    var synced = 0;
    for (final row in rows) {
      final localUuid = row['local_uuid'].toString();
      try {
        final payload = jsonDecode(row['payload'].toString()) as Map<String, dynamic>;
        final response = await api.postJson('fiches/store.php', payload);
        if (response['success'] == true) {
          final fiche = response['fiche'] as Map<String, dynamic>? ?? {};
          await LocalDatabase.instance.markTicketSynced(
            localUuid,
            int.tryParse('${fiche['id']}') ?? 0,
            '${fiche['fiche_code'] ?? ''}',
          );
          await LocalDatabase.instance.addSyncLog(
            localUuid: localUuid,
            entityType: 'ticket',
            status: 'synced',
            message: 'Ticket synchronisé: ${fiche['fiche_code'] ?? ''}',
          );
          synced++;
        } else {
          final msg = response['message']?.toString() ?? 'Sync failed';
          await LocalDatabase.instance.markTicketFailed(localUuid, msg);
          await LocalDatabase.instance.addSyncLog(localUuid: localUuid, entityType: 'ticket', status: 'failed', message: msg);
        }
      } catch (e) {
        await LocalDatabase.instance.markTicketFailed(localUuid, e.toString());
        await LocalDatabase.instance.addSyncLog(localUuid: localUuid, entityType: 'ticket', status: 'failed', message: e.toString());
      }
    }
    return synced;
  }
}
