// SQLite local database plan for Mobile v2.
// Recommended package: sqflite

class LocalDatabaseSchema {
  static const ticketsTable = '''
  CREATE TABLE IF NOT EXISTS local_tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    local_uuid TEXT UNIQUE NOT NULL,
    server_ticket_id INTEGER,
    tenant_id INTEGER,
    agent_id INTEGER,
    payload TEXT NOT NULL,
    total_amount REAL DEFAULT 0,
    sync_status TEXT DEFAULT 'pending_sync',
    error_message TEXT,
    created_at TEXT,
    synced_at TEXT
  );
  ''';

  static const syncLogsTable = '''
  CREATE TABLE IF NOT EXISTS sync_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    local_uuid TEXT,
    entity_type TEXT,
    status TEXT,
    message TEXT,
    created_at TEXT
  );
  ''';
}
