<?php

namespace App\Services;

/**
 * Resultado inmutable de una resolución logística
 * (LogisticsResolutionService). Nunca representa una acción
 * ejecutada — solo el resultado de una lectura.
 */
final class LogisticsResolutionResult
{
    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_NO_COVERAGE = 'no_coverage';

    public const STATUS_AMBIGUOUS = 'ambiguous';

    public const STATUS_INVALID = 'invalid';

    public const RULE_CITY = 'city';

    public const RULE_STATE = 'state';

    private function __construct(
        public readonly string $status,
        public readonly ?int $warehouseId,
        public readonly ?string $rule,
        public readonly string $reason,
    ) {
    }

    public static function resolved(int $warehouseId, string $rule, string $reason): self
    {
        return new self(self::STATUS_RESOLVED, $warehouseId, $rule, $reason);
    }

    public static function noCoverage(string $reason): self
    {
        return new self(self::STATUS_NO_COVERAGE, null, null, $reason);
    }

    public static function ambiguous(string $reason): self
    {
        return new self(self::STATUS_AMBIGUOUS, null, null, $reason);
    }

    public static function invalid(string $reason): self
    {
        return new self(self::STATUS_INVALID, null, null, $reason);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
