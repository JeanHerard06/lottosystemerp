# Mobile v2 — Agent MVP

## Objectif
Construire une application Flutter offline-first pour les agents: login, cash session, vente ticket, impression Bluetooth, vérification QR, sync.

## Fonctionnalités MVP
- Auth token + refresh token
- Dashboard agent
- Ouvrir/fermer cash session
- Créer ticket
- Reprint ticket
- Scanner QR ticket
- Voir résultats/tirages
- Notifications
- SQLite local + sync queue
- Bluetooth printer 58/80mm

## Branch recommandée
```bash
git checkout develop/v2.0
git pull origin develop/v2.0
git checkout -b feature/mobile-v2-agent-mvp
```

## API nécessaires
- POST /api/v2/auth/login
- POST /api/v2/auth/refresh
- GET /api/v2/mobile/dashboard
- GET /api/v2/mobile/cash/current
- POST /api/v2/mobile/cash/open
- POST /api/v2/mobile/cash/close
- POST /api/v2/mobile/tickets
- GET /api/v2/mobile/tickets
- GET /api/v2/mobile/tickets/{id}
- POST /api/v2/mobile/tickets/{id}/reprint
- POST /api/v2/mobile/tickets/verify
- GET /api/v2/mobile/lotteries
- GET /api/v2/mobile/results
- GET /api/v2/mobile/notifications
- POST /api/v2/mobile/sync

## Règles métier
1. Agent ne peut pas vendre sans cash session ouverte.
2. Agent ne peut pas vendre sur lottery fermée/drawn.
3. Ticket offline doit être marqué `sync_status=pending` puis envoyé au serveur.
4. Ticket synchronisé reçoit un `server_ticket_id` et un `verification_code` serveur.
5. Toute action mobile doit contenir `device_id`.
6. Toute requête API doit respecter `tenant_id` côté serveur.
