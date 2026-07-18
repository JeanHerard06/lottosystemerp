# Changelog — Mobile Functional MVP

## Added
- Mobile API endpoints for lotteries, cash sessions and ticket verification.
- Functional Flutter login/dashboard/ticket/cash/balance/tirages screens.
- SQLite local queue for offline tickets.
- Manual sync for pending offline tickets.
- QR/code ticket verification screen.

## Fixed
- Mobile app no longer opens mock login/dashboard by default.
- API client handles Bearer token, timeout and invalid API responses.
- Mobile ticket creation now supports `local_uuid` for offline sync conflict prevention.

## Notes
- Bluetooth printing is still a hardware integration phase.
- Firebase push notifications are not yet enabled.
