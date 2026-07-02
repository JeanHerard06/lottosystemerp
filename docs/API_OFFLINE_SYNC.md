# API v2 Offline Sync Contract

## POST /api/v2/tickets
Mobile app sends local_uuid, device_id, ticket lines, lottery id, cash session id, and totals.

## Idempotency
The server must use tenant_id + agent_id + local_uuid to prevent duplicate ticket creation.

## Response
```json
{
  "success": true,
  "server_ticket_id": 123,
  "ticket_code": "FCH-20260702-000123",
  "sync_status": "synced"
}
```

## POST /api/v2/sync/logs
Mobile app can submit failed sync logs for support review.
