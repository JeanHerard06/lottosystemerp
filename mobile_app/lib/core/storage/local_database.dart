import 'dart:convert';
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

class LocalDatabase {
  LocalDatabase._();
  static final LocalDatabase instance = LocalDatabase._();
  Database? _db;

  Future<Database> get db async {
    if (_db != null) return _db!;
    final path = p.join(await getDatabasesPath(), 'lotto_erp_mobile.db');
    _db = await openDatabase(path, version: 1, onCreate: (database, version) async {
      await database.execute('''
        CREATE TABLE IF NOT EXISTS local_tickets (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          local_uuid TEXT UNIQUE NOT NULL,
          server_ticket_id INTEGER,
          fiche_code TEXT,
          payload TEXT NOT NULL,
          total_amount REAL DEFAULT 0,
          sync_status TEXT DEFAULT 'pending_sync',
          error_message TEXT,
          created_at TEXT,
          synced_at TEXT
        )
      ''');
      await database.execute('''
        CREATE TABLE IF NOT EXISTS sync_logs (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          local_uuid TEXT,
          entity_type TEXT,
          status TEXT,
          message TEXT,
          created_at TEXT
        )
      ''');
    });
    return _db!;
  }

  Future<void> savePendingTicket({
    required String localUuid,
    required Map<String, dynamic> payload,
    required double totalAmount,
    String? errorMessage,
  }) async {
    final database = await db;
    await database.insert(
      'local_tickets',
      {
        'local_uuid': localUuid,
        'payload': jsonEncode(payload),
        'total_amount': totalAmount,
        'sync_status': 'pending_sync',
        'error_message': errorMessage,
        'created_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<List<Map<String, dynamic>>> pendingTickets() async {
    final database = await db;
    return database.query(
      'local_tickets',
      where: "sync_status IN ('pending_sync','failed')",
      orderBy: 'id ASC',
    );
  }

  Future<int> pendingCount() async {
    final database = await db;
    final rows = await database.rawQuery("SELECT COUNT(*) AS total FROM local_tickets WHERE sync_status IN ('pending_sync','failed')");
    return (rows.first['total'] as int?) ?? 0;
  }

  Future<void> markTicketSynced(String localUuid, int serverId, String ficheCode) async {
    final database = await db;
    await database.update(
      'local_tickets',
      {
        'server_ticket_id': serverId,
        'fiche_code': ficheCode,
        'sync_status': 'synced',
        'synced_at': DateTime.now().toIso8601String(),
        'error_message': null,
      },
      where: 'local_uuid = ?',
      whereArgs: [localUuid],
    );
  }

  Future<void> markTicketFailed(String localUuid, String message) async {
    final database = await db;
    await database.update(
      'local_tickets',
      {'sync_status': 'failed', 'error_message': message},
      where: 'local_uuid = ?',
      whereArgs: [localUuid],
    );
  }

  Future<List<Map<String, dynamic>>> recentLocalTickets() async {
    final database = await db;
    return database.query('local_tickets', orderBy: 'id DESC', limit: 100);
  }

  Future<void> addSyncLog({
    String? localUuid,
    required String entityType,
    required String status,
    required String message,
  }) async {
    final database = await db;
    await database.insert('sync_logs', {
      'local_uuid': localUuid,
      'entity_type': entityType,
      'status': status,
      'message': message,
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  Future<List<Map<String, dynamic>>> recentSyncLogs({int limit = 50}) async {
    final database = await db;
    return database.query('sync_logs', orderBy: 'id DESC', limit: limit);
  }

}
