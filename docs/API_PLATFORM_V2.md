# API Platform v2

## Objective
Provide a stable API layer for mobile app, customer portal, BI dashboard, marketplace plugins and external integrations.

## Base URL
/api/v2

## Core modules
- /auth
- /users
- /agents
- /agencies
- /lotteries
- /games
- /schedules
- /tickets
- /results
- /gains
- /cash-sessions
- /reports
- /customers
- /notifications
- /webhooks

## Security
- API key for application/client identification.
- Bearer access token for authenticated users.
- Refresh token rotation.
- Tenant scope enforced on every request.
- Role and permission checks for protected endpoints.

## Response format
```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

## Error format
```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": []
}
```
