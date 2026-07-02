# Mobile v2 — QR Verification + Claims

## Objectif
Permettre aux agents et superviseurs de scanner un ticket, vérifier son authenticité, contrôler son statut de gain et soumettre une demande de paiement.

## Workflow
1. Scanner QR Code
2. Appeler `/api/v2/tickets/verify`
3. Afficher statut: valid, invalid, paid, cancelled, suspicious
4. Si gagnant non payé, soumettre claim via `/api/v2/claims/store`
5. Bloquer double paiement
6. Créer audit log et fraude alert si nécessaire

## Sécurité
- Tenant isolation obligatoire
- Agent doit avoir une cash session ouverte pour payer
- Superviseur peut valider/rejeter une claim selon permissions
- Toute tentative cross-tenant doit être auditée
