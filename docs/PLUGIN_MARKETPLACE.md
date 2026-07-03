# Plugin / Marketplace Foundation — v2.0

## Objectif
Permettre d'ajouter des modules sans modifier le core Lotto ERP Enterprise.

Exemples de plugins:
- MonCash
- NatCash
- Stripe
- PayPal
- WhatsApp
- SMS Gateway
- AI Analytics
- Advanced Reports

## Concepts

### plugins
Catalogue global des plugins disponibles dans la plateforme.

### tenant_plugins
Activation/désactivation d'un plugin pour un tenant.

### plugin_settings
Paramètres configurables par tenant et par plugin.

### plugin_events
Événements émis par le core pour déclencher des hooks.

## Workflow

```text
Installer plugin
   ↓
Enregistrer plugin
   ↓
Activer pour tenant
   ↓
Configurer settings
   ↓
Écouter events
   ↓
Exécuter hooks
```

## Event Hooks proposés
- TicketCreated
- TicketCancelled
- GainCalculated
- GainPaid
- CashSessionOpened
- CashSessionClosed
- LotteryClosed
- LotteryReopened
- TenantCreated
- SubscriptionPaid

## Structure d'un plugin

```text
plugins/whatsapp/
├── plugin.json
├── src/
│   ├── Plugin.php
│   ├── Listeners/
│   └── Services/
└── views/
```

## Exemple plugin.json

```json
{
  "code": "whatsapp",
  "name": "WhatsApp Notifications",
  "version": "1.0.0",
  "description": "Send WhatsApp alerts for tickets, gains, and risk events.",
  "requires": ["notifications"],
  "events": ["TicketCreated", "GainPaid", "LotteryClosed"]
}
```

## Sécurité
- Un plugin ne doit jamais contourner tenant isolation.
- Chaque lecture/écriture doit recevoir `tenant_id`.
- Les secrets doivent rester dans `plugin_settings`, jamais dans le code.
- Seul `super_admin` installe/désinstalle un plugin global.
- `tenant_admin` peut uniquement activer/configurer les plugins autorisés par son plan.

## Permissions proposées
- plugins.view
- plugins.manage
- plugins.install
- plugins.enable
- plugins.configure
