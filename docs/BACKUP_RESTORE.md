# Backup & Restore Guide

## Backup Types
- Database SQL dump
- Uploads archive
- Tenant settings export
- Full application snapshot

## Backup Policy
- Daily database backup
- Weekly full backup
- Retain daily backups for 14 days
- Retain weekly backups for 8 weeks

## Manual Backup
```bash
php scripts/backup_database.php
```

## Restore Checklist
1. Put app in maintenance mode.
2. Backup current database.
3. Restore selected SQL file.
4. Restore uploads if needed.
5. Run `upgrade.php` if database version changed.
6. Run health check.
7. Disable maintenance mode.

## Security Notes
- Do not expose backup files publicly.
- Store production backups outside web root.
- Encrypt offsite backups.
