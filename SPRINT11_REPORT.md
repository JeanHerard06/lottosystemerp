# Sprint 11 — Paiements & Abonnements SaaS

## Livrables
- Plans SaaS CRUD: Basic, Professional, Enterprise et plans personnalisés.
- Factures d’abonnement par tenant.
- Paiements d’abonnement avec méthodes Cash, MonCash, NatCash, Stripe, PayPal.
- Mise à jour automatique du statut facture: issued, partial, paid.
- Renouvellement tenant lors du paiement complet d’une facture.
- Dashboard abonnements: tenants actifs, expirés, montant à recevoir, payé ce mois.
- Permissions: plans.manage, subscriptions.manage, payments.view, payments.create.
- Migration: database/migrations/011_sprint11_subscriptions_payments.sql.
- Audit logs sur création/modification plan, facture et paiement.

## Notes techniques
- Le Sprint 11 reste compatible avec la fondation multi-tenant du Sprint 10.
- Les intégrations Stripe/PayPal/MonCash/NatCash sont préparées côté modèle, mais les appels API externes restent à configurer selon les comptes marchands réels.
