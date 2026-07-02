# Mobile v2 — Offline Sync + Bluetooth Printing

Objectif: permettre aux agents de vendre et réimprimer des tickets même lorsque la connexion est instable, puis synchroniser automatiquement les ventes quand Internet revient.

Fonctionnalités:
- Stockage local SQLite
- Queue de synchronisation des tickets
- Statuts: pending_sync, synced, failed
- Reprint local ticket
- Impression Bluetooth 58mm / 80mm
- QR code / verification code sur ticket
- Journal des erreurs de synchronisation

Workflow ticket offline:
1. Agent crée ticket.
2. Ticket est sauvegardé localement avec pending_sync.
3. Ticket peut être imprimé immédiatement.
4. Sync service tente l’envoi à /api/v2/tickets.
5. Si succès: statut synced, serveur renvoie server_ticket_id.
6. Si échec: statut failed avec message.

Règles importantes:
- Ne jamais perdre un ticket local.
- Un ticket offline doit avoir un local_uuid unique.
- Le serveur doit refuser les doublons sur local_uuid + tenant_id + agent_id.
- Le ticket imprimé offline doit afficher OFFLINE/PENDING jusqu’à synchronisation.
