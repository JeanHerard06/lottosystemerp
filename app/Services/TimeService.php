<?php

declare(strict_types=1);

final class TimeService
{
    private static string $timezone = 'America/Port-au-Prince';
    private static ?PDO $pdo = null;
    private static ?int $tenantId = null;

    public static function boot(PDO $pdo, ?int $tenantId = null, ?string $timezone = null): string
    {
        self::$pdo = $pdo;
        self::$tenantId = $tenantId;
        $resolved = $timezone ?: self::resolveTenantTimezone($pdo, $tenantId);

        try {
            new DateTimeZone($resolved);
        } catch (Throwable) {
            $resolved = self::fallbackTimezone();
        }

        self::$timezone = $resolved;
        date_default_timezone_set($resolved);
        self::applyDatabaseTimezone($pdo, $resolved);

        return $resolved;
    }

    public static function configure(string $timezone): string
    {
        try {
            new DateTimeZone($timezone);
        } catch (Throwable) {
            $timezone = self::fallbackTimezone();
        }
        self::$timezone = $timezone;
        date_default_timezone_set($timezone);
        return $timezone;
    }

    public static function timezone(): string
    {
        return self::$timezone;
    }

    public static function tenantId(): ?int
    {
        return self::$tenantId;
    }

    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::$timezone));
    }

    public static function today(): string
    {
        return date('Y-m-d');
    }

    public static function sqlNow(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * SQL-safe bounds for the current tenant day.
     *
     * This method deliberately returns plain strings and does not call
     * DateTime::format() on values returned by other helpers. It is the
     * compatibility boundary used by dashboard/report SQL queries.
     *
     * @return array{start:string,end:string,date:string}
     */
    public static function sqlDayBounds(): array
    {
        return [
            'start' => date('Y-m-d 00:00:00'),
            'end' => date('Y-m-d 23:59:59'),
            'date' => date('Y-m-d'),
        ];
    }

    /**
     * Start of the current tenant day as a SQL datetime string.
     */
    public static function todayStart(): string
    {
        return self::sqlDayBounds()['start'];
    }

    /**
     * End of the current tenant day as a SQL datetime string.
     */
    public static function todayEnd(): string
    {
        return self::sqlDayBounds()['end'];
    }

    /**
     * Start of the current tenant day as a typed DateTimeImmutable.
     */
    public static function todayStartDateTime(): DateTimeImmutable
    {
        return self::now()->setTime(0, 0, 0);
    }

    /**
     * End of the current tenant day as a typed DateTimeImmutable.
     */
    public static function todayEndDateTime(): DateTimeImmutable
    {
        return self::now()->setTime(23, 59, 59);
    }

    public static function monthStart(): string
    {
        return self::now()->modify('first day of this month')->format('Y-m-d');
    }

    public static function normalizeDate(?string $value, ?string $fallback = null): string
    {
        $candidate = trim((string)$value);
        if ($candidate !== '') {
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $candidate, new DateTimeZone(self::$timezone));
            $errors = DateTimeImmutable::getLastErrors();
            if ($parsed && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $parsed->format('Y-m-d');
            }
        }
        return $fallback ?: self::today();
    }

    public static function parse(string $value, ?string $timezone = null): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone($timezone ?: self::$timezone));
    }

    public static function at(string $date, string $time): DateTimeImmutable
    {
        return self::parse(trim($date) . ' ' . trim($time));
    }

    public static function toUtc(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTimezone(new DateTimeZone('UTC'));
    }

    public static function fromUtc(string $value): DateTimeImmutable
    {
        return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone(self::$timezone));
    }

    public static function meta(): array
    {
        $now = self::now();
        return [
            'timezone' => self::$timezone,
            'utc_offset' => $now->format('P'),
            'server_time' => $now->format(DateTimeInterface::ATOM),
        ];
    }

    private static function resolveTenantTimezone(PDO $pdo, ?int $tenantId): string
    {
        if ($tenantId && self::hasTable($pdo, 'tenant_settings')) {
            try {
                $stmt = $pdo->prepare("SELECT setting_value FROM tenant_settings WHERE tenant_id=? AND setting_key='timezone' LIMIT 1");
                $stmt->execute([$tenantId]);
                $value = trim((string)$stmt->fetchColumn());
                if ($value !== '') {
                    return $value;
                }
            } catch (Throwable) {
                // Fall through to tenant column / application default.
            }
        }

        if ($tenantId && self::hasColumn($pdo, 'tenants', 'timezone')) {
            try {
                $stmt = $pdo->prepare('SELECT timezone FROM tenants WHERE id=? LIMIT 1');
                $stmt->execute([$tenantId]);
                $value = trim((string)$stmt->fetchColumn());
                if ($value !== '') {
                    return $value;
                }
            } catch (Throwable) {
                // Fall through to application default.
            }
        }

        return self::fallbackTimezone();
    }

    private static function fallbackTimezone(): string
    {
        if (function_exists('env_value')) {
            return (string)env_value('APP_TIMEZONE', 'America/Port-au-Prince');
        }
        return getenv('APP_TIMEZONE') ?: 'America/Port-au-Prince';
    }

    private static function applyDatabaseTimezone(PDO $pdo, string $timezone): void
    {
        try {
            $offset = (new DateTimeImmutable('now', new DateTimeZone($timezone)))->format('P');
            $pdo->exec("SET time_zone = " . $pdo->quote($offset));
        } catch (Throwable) {
            // PHP remains the source of truth when MySQL timezone tables are unavailable.
        }
    }

    private static function hasTable(PDO $pdo, string $table): bool
    {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
            $stmt->execute([$table]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private static function hasColumn(PDO $pdo, string $table, string $column): bool
    {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
            $stmt->execute([$table, $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }
}
