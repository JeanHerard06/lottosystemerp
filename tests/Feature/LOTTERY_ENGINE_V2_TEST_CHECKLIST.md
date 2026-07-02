# Lottery Engine v2 Test Checklist

- [ ] Tenant A cannot see Tenant B games
- [ ] Agent cannot sell without open cash session
- [ ] Agent cannot sell outside sales window
- [ ] Manual close blocks sales immediately
- [ ] Auto close cron closes windows at cutoff
- [ ] Result validation locks draw
- [ ] Gain calculation is idempotent
- [ ] Audit logs are created for close/reopen/result/calculate
