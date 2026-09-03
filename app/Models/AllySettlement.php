<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllySettlement extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REVERSED = 'reversed';

    public const PAYMENT_METHODS = [
        'transferencia',
        'pago_movil',
        'efectivo_usd',
        'efectivo_ves',
        'zelle',
        'otro',
    ];

    protected $fillable = [
        'ally_id',
        'amount_usd',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
        'requested_by_user_id',
        'paid_by_user_id',
        'reversed_by_user_id',
        'requested_at',
        'paid_at',
        'reversed_at',
        'reversal_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'requested_at' => 'datetime',
            'paid_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(
            Ally::class
        );
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requested_by_user_id'
        );
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'paid_by_user_id'
        );
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reversed_by_user_id'
        );
    }

    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(
            AllyFinancialTransaction::class,
            'reversal_transaction_id'
        );
    }

    public function isPending(): bool
    {
        return $this->status
            === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status
            === self::STATUS_PAID;
    }

    public function isReversed(): bool
    {
        return $this->status
            === self::STATUS_REVERSED;
    }

    public function isCancelled(): bool
    {
        return $this->status
            === self::STATUS_CANCELLED;
    }
}
