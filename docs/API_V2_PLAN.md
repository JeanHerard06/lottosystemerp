# API v2 Plan

Objectif: préparer une API versionnée, sécurisée et stable pour PWA, Flutter et intégrations externes.

## Principes
- Préfixe: `/api/v2`
- Réponses JSON standardisées
- Authentification token/JWT
- Tenant isolation obligatoire
- Rate limiting par tenant et utilisateur
- Logs API pour audit

## Endpoints prioritaires
- `POST /api/v2/auth/login`
- `GET /api/v2/me`
- `GET /api/v2/dashboard`
- `GET /api/v2/lotteries`
- `GET /api/v2/lottery-schedules`
- `POST /api/v2/fiches`
- `GET /api/v2/fiches/{id}`
- `POST /api/v2/tickets/verify`
- `GET /api/v2/results`
- `GET /api/v2/agent/balance`

## Format réponse
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```
