<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllyFinancialTransaction extends Model
{
    use HasFactory;

    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT = 'debit';

    public const TYPE_COMMISSION = 'commission';
    public const TYPE_SETTLEMENT = 'settlement';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_REVERSAL = 'reversal';

    protected $fillable = [
        'ally_id',
        'direction',
        'type',
        'amount_usd',
        'source_type',
        'source_id',
        'reversed_transaction_id',
        'reference',
        'description',
        'metadata',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reversed_transaction_id'
        );
    }

    public function isCredit(): bool
    {
        return $this->direction
            === self::DIRECTION_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->direction
            === self::DIRECTION_DEBIT;
    }

    /*
     * El ledger es inmutable.
     *
     * Nunca debemos modificar ni borrar una operación
     * financiera existente. Para corregirla se crea
     * otro movimiento.
     */
    protected static function booted(): void
    {
        static::updating(function (): bool {
            throw new \LogicException(
                'Los movimientos financieros son inmutables.'
            );
        });

        static::deleting(function (): bool {
            throw new \LogicException(
                'Los movimientos financieros no pueden eliminarse.'
            );
        });
    }
}
