# Payment Gateway Plugins — v2.0

Objectif: gérer les paiements SaaS et les renouvellements d'abonnement via plugins.

## Gateways inclus
- MonCash
- NatCash
- Stripe
- PayPal
- Paiement manuel / cash

## Workflow
1. Tenant reçoit une facture.
2. Système crée une tentative de paiement.
3. Plugin gateway initialise le paiement.
4. Gateway confirme via webhook ou vérification manuelle.
5. Facture passe à `paid`.
6. Abonnement tenant est renouvelé.
7. Audit log + notification sont créés.

## Méthodes standard
Chaque plugin doit exposer:

```php
createPayment(array $invoice): array;
verifyPayment(string $reference): array;
handleWebhook(array $payload): array;
refundPayment(string $reference, float $amount): array;
```

## Sécurité
- Vérifier signature webhook.
- Ne jamais stocker secrets en clair dans le code.
- Secrets dans `.env` ou `plugin_settings` chiffrés.
- Toutes les requêtes doivent respecter `tenant_id`.
- Tous les paiements doivent créer un audit log.

## Statuts paiement
- pending
- processing
- paid
- failed
- cancelled
- refunded

## Tables principales
- payment_gateways
- payment_attempts
- payment_webhook_logs
- payment_refunds
