<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AllyFinancialTransaction;

class PaymentOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVERSED = 'reversed';

    public const METHOD_PAGO_MOVIL = 'pago_movil';
    public const METHOD_AUTOMATIC_DEBIT = 'automatic_debit';

    public const PURPOSE_ALLY_DEBT = 'ally_debt';
    public const PURPOSE_PACKAGE = 'package';
    public const PURPOSE_COD = 'cod';
    public const PURPOSE_OTHER = 'other';

    protected $fillable = [
        'order_number',
        'payer_type',
        'payer_id',
        'purpose',
        'ally_id',
        'package_id',
        'amount_usd',
        'payment_method',
        'status',
        'bank_reference',
        'bank_code',
        'bank_name',
        'confirmed_at',
        'expires_at',
        'metadata',
        'created_by_user_id',
        'confirmed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'metadata' => 'array',
            'confirmed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id'
        );
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by_user_id'
        );
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
    public function financialTransactions(): HasMany
    {
        return $this->hasMany(
            AllyFinancialTransaction::class,
            'payment_order_id'
        );
    }
    public function canBeConfirmed(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING,
                self::STATUS_PROCESSING,
            ],
            true
        );
    }
}
