# Cron Setup

Add these jobs on production:

```cron
* * * * * php /var/www/lottoerp/cron/auto_close_lotteries.php >> /var/www/lottoerp/storage/logs/cron.log 2>&1
*/5 * * * * php /var/www/lottoerp/cron/notifications_dispatch.php >> /var/www/lottoerp/storage/logs/cron.log 2>&1
0 2 * * * php /var/www/lottoerp/scripts/backup_database.php >> /var/www/lottoerp/storage/logs/backup.log 2>&1
0 3 * * 0 php /var/www/lottoerp/cron/purge_old_logs.php >> /var/www/lottoerp/storage/logs/cron.log 2>&1
```

Recommended: protect cron endpoints with CLI-only execution or a secure CRON_TOKEN.
