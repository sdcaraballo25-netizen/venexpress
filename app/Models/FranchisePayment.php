<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pago de la cuota de franquicia, registrado manualmente por un Admin
 * contra una FranchiseInvoice (con su N° de voucher/referencia).
 */
class FranchisePayment extends Model
{
    protected $fillable = [
        'franchise_invoice_id',
        'ally_id',
        'amount_paid_usd',
        'payment_reference',
        'paid_on',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid_usd' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FranchiseInvoice::class, 'franchise_invoice_id');
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
