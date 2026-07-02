# Lottery Engine 2.0

## Objective
Transform lotteries from simple CRUD records into a configurable engine supporting multiple games, schedules, sales windows, draw results, prize rules, and payout workflows.

## Core Flow
Lottery -> Game -> Schedule -> Sales Window -> Draw -> Result -> Prize Rule -> Gain Calculation -> Payout.

## Rules
- A tenant can only manage its own lotteries, games, schedules, draws, and results.
- Super admin can see all tenants.
- No ticket can be sold outside an open sales window.
- Draws must be locked after result validation.
- Gain calculation must be idempotent.
- Every critical action must create an audit log.

## Modules
- Games: Pick2, Pick3, Pick4, Borlette, Mariage, custom.
- Schedules: daily/weekly schedule with holiday exceptions.
- Sales windows: open/close by date and time.
- Draws: generated from schedules or created manually.
- Results: attached to a draw.
- Prize rules: configurable payout multipliers.
- Payout: paid/unpaid/voided states.

## Implementation Branch
`feature/lottery-engine-v2`
