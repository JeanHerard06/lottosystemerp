# Mobile Agent Stabilization Report

This patch focuses on making the Agent Mobile MVP testable end-to-end against the `master` backend.

## Stabilized workflows

1. Login / logout using `api/mobile/login.php` and Bearer token.
2. Agent dashboard with daily tickets, sales, gains and balance.
3. Cash session open / close.
4. Ticket creation with multiple play lines.
5. Offline ticket queue using SQLite.
6. Manual sync of pending or failed offline tickets.
7. Ticket list: server tickets + local sync status.
8. Ticket QR/code verification.
9. Claim submission for winning tickets.
10. Claim history for the logged-in tenant/agent.

## Backend endpoints added

- `api/mobile/claims_store.php`
- `api/mobile/claims_list.php`

The claim endpoints are defensive against the two historical claim table variants:

- `ticket_id` / `amount` / `notes`
- `fiche_id` / `claim_amount` / `comment`

## Flutter files added/updated

- `mobile_app/lib/screens/claims_screen.dart`
- `mobile_app/lib/screens/dashboard_screen.dart`
- `mobile_app/lib/screens/qr_verify_screen.dart`
- `mobile_app/lib/config.dart`

## Important local config

For Android emulator with backend running on your computer at `localhost:8081`, use:

```dart
const String apiBaseUrl = 'http://10.0.2.2:8081/api/mobile';
```

For a physical Android phone, replace `10.0.2.2` with your computer LAN IP:

```dart
const String apiBaseUrl = 'http://192.168.1.25:8081/api/mobile';
```

## Manual QA checklist

### Backend

- Install database using `install.php` on an empty database.
- Login web as admin.
- Create tenant, agency, agent, lottery.
- Open cash session for the agent.

### Mobile

- Run `flutter pub get` in `mobile_app/`.
- Run app on emulator.
- Login agent.
- Open cash session if not open.
- Create ticket online.
- Disable server/network and create ticket offline.
- Re-enable server/network and press Sync.
- Verify ticket by code.
- Submit claim for a winning ticket.
- Close cash session.

## Known next improvements

- Add real Bluetooth printer package integration.
- Add automatic background sync with connectivity detection.
- Add Firebase Cloud Messaging for push notifications.
- Add Supervisor Mobile module after Agent MVP is stable.
