# Webhooks v2

## Events
- ticket.created
- ticket.cancelled
- lottery.closed
- lottery.reopened
- draw.resulted
- gain.calculated
- gain.paid
- cash_session.opened
- cash_session.closed
- fraud.alert.created

## Delivery
Webhook payloads should be signed using HMAC SHA256.

## Retry policy
- 1 min
- 5 min
- 15 min
- 1 hour
- mark failed after max attempts
