# API v2 Mobile Spec

## Auth
### POST /api/v2/auth/login
Request:
```json
{"username":"agent1","password":"secret","device_id":"DEVICE-123"}
```
Response:
```json
{"success":true,"access_token":"...","refresh_token":"...","user":{"id":1,"role":"agent","tenant_id":1}}
```

## Dashboard
### GET /api/v2/mobile/dashboard
Headers: `Authorization: Bearer <token>`
Response:
```json
{"success":true,"data":{"sales_today":0,"commission_today":0,"balance":0,"open_session":true,"notifications":0}}
```

## Cash Sessions
### POST /api/v2/mobile/cash/open
```json
{"opening_amount":1000,"device_id":"DEVICE-123"}
```

### POST /api/v2/mobile/cash/close
```json
{"closing_amount":2500,"notes":"Fin journée","device_id":"DEVICE-123"}
```

## Tickets
### POST /api/v2/mobile/tickets
```json
{
  "client_uuid":"optional",
  "device_id":"DEVICE-123",
  "local_uuid":"LOCAL-TICKET-001",
  "lottery_id":1,
  "lines":[
    {"game_type":"borlette","number_played":"45","amount":50},
    {"game_type":"mariage","number_played":"12-45","amount":25}
  ]
}
```

## Sync
### POST /api/v2/mobile/sync
```json
{"device_id":"DEVICE-123","tickets":[...],"events":[...]}
```
