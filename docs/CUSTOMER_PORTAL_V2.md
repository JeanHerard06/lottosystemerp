# Customer Portal v2

## Objectif
Permettre aux clients finaux de vérifier leurs tickets, consulter les résultats, soumettre une réclamation de gain et télécharger une copie PDF/numérique de leur ticket.

## Modules
- Customer registration/login
- Ticket verification by code/QR
- Ticket history
- Result lookup
- Claim workflow
- Notifications

## Workflow verification
1. Customer enters ticket code or scans QR.
2. System validates tenant, ticket status, lottery/draw, and signature.
3. System displays ticket details without exposing internal agent data.
4. If winning ticket, customer can submit a claim.
5. Tenant staff reviews and pays according to permissions.

## Security Rules
- Tenant isolation is mandatory.
- Customers only see their own tickets unless using public verification code.
- Ticket verification must never expose internal financial ledger entries.
- Claims require audit logs.
- QR code should include a signed verification token, not raw database IDs.

## API v2 Endpoints Draft
- POST /api/v2/customer/register
- POST /api/v2/customer/login
- GET /api/v2/customer/tickets
- GET /api/v2/customer/tickets/{code}
- POST /api/v2/customer/claims
- GET /api/v2/customer/results

## Next Implementation Tasks
- Build CustomerAuthService
- Build TicketVerificationService
- Build ClaimService
- Add public ticket verification page
- Add tenant claim review dashboard
