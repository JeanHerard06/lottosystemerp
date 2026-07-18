<?php
require_once __DIR__ . '/../Helpers/gains.php';

final class GameEngineService
{
    public function __construct(private PDO $pdo) {}

    public function enabledGames(?int $tenantId): array
    {
        return game_engine_types($this->pdo, $tenantId, true);
    }

    public function validate(string $gameCode, string $number, ?int $tenantId): ?string
    {
        return game_engine_validate_play($this->pdo, $gameCode, $number, $tenantId);
    }

    public function calculate(array $detail, array $tirage): array
    {
        return calculate_detail_gain($this->pdo, $detail, $tirage);
    }
}
