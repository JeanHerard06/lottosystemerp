<?php

declare(strict_types=1);

require_once __DIR__ . '/TimeService.php';

final class LotteryTimeService
{
    public static function drawAt(array $lottery, ?string $date = null): ?DateTimeImmutable
    {
        $drawTime = trim((string)($lottery['draw_time'] ?? ''));
        if ($drawTime === '') {
            return null;
        }
        return TimeService::at($date ?: TimeService::today(), $drawTime);
    }

    public static function closeAt(array $lottery, ?string $date = null): ?DateTimeImmutable
    {
        $drawAt = self::drawAt($lottery, $date);
        if (!$drawAt) {
            return null;
        }
        $minutes = max(0, (int)($lottery['close_before_minutes'] ?? 10));
        return $minutes > 0 ? $drawAt->modify("-{$minutes} minutes") : $drawAt;
    }

    public static function isSalesClosed(array $lottery, ?DateTimeInterface $now = null): bool
    {
        if (($lottery['sales_status'] ?? 'open') !== 'open') {
            return true;
        }
        $closeAt = self::closeAt($lottery);
        if (!$closeAt) {
            return false;
        }
        $current = $now ? DateTimeImmutable::createFromInterface($now) : TimeService::now();
        return $current >= $closeAt;
    }

    public static function isDrawDue(array $lottery, ?DateTimeInterface $now = null): bool
    {
        $drawAt = self::drawAt($lottery);
        if (!$drawAt) {
            return false;
        }
        $current = $now ? DateTimeImmutable::createFromInterface($now) : TimeService::now();
        return $current >= $drawAt;
    }
}
