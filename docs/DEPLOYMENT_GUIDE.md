# Deployment Guide — Lotto ERP Enterprise v1.0 RC1

## Environments
- Local: development and testing
- Demo: customer demonstrations
- Production: live tenants

## Minimum Requirements
- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.6+
- Apache/Nginx with rewrite support
- PHP extensions: pdo_mysql, mbstring, openssl, json, fileinfo, gd/imagick recommended
- Writable folders: `storage/`, `public/uploads/`, `backups/`

## Recommended Deployment Flow
1. Clone repository.
2. Copy `.env.example` to `.env`.
3. Configure database, app URL, timezone, SMTP, and backup path.
4. Run `install.php` for first install or `upgrade.php` for update.
5. Configure cron jobs.
6. Run health check.
7. Create first super admin.
8. Create first tenant and tenant admin.

## Folder Permissions
```bash
chmod -R 775 storage public/uploads backups
```

## Apache VirtualHost Example
```apache
<VirtualHost *:80>
    ServerName demo.lottoerp.local
    DocumentRoot /var/www/lottoerp/public

    <Directory /var/www/lottoerp/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx Example
```nginx
server {
    listen 80;
    server_name demo.lottoerp.local;
    root /var/www/lottoerp/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```
