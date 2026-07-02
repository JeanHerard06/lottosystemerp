# Customer Portal (v1.1 Ready)

Objectif: permettre aux clients de vérifier un ticket, consulter les résultats et recevoir une preuve digitale.

## Modules
- Ticket verification par code, QR code ou barcode
- Résultats publics par lottery/date
- Historique ticket si customer account activé
- Page publique: `/public/verify_ticket.php?code=...`

## Règles
- Un ticket annulé ne doit jamais être validé comme gagnant.
- Un ticket payé doit afficher `PAID` pour éviter double paiement.
- La vérification doit respecter le tenant via code signé.

## Permissions
- `customers.view`
- `customers.manage`
- `tickets.verify`
