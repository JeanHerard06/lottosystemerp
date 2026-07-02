# Health Check Specification

The health check should verify:

- PHP version
- Required PHP extensions
- Database connection
- Writable folders
- Disk free space
- Upload size limit
- Memory limit
- Cron latest run
- Pending migrations
- Open cash sessions older than expected
- Failed jobs / failed cron runs

Status levels:
- OK
- WARNING
- CRITICAL
