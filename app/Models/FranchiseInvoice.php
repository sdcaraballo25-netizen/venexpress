<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Factura mensual de la cuota de franquicia (cuentas por cobrar). Ver
 * App\Services\FranchiseBillingService.
 */
class FranchiseInvoice extends Model
{
    protected $fillable = [
        'ally_id',
        'period_month',
        'invoice_number',
        'sales_total_usd',
        'fee_percentage',
        'expected_amount_usd',
        'invoice_amount_usd',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'sales_total_usd' => 'decimal:2',
            'fee_percentage' => 'decimal:2',
            'expected_amount_usd' => 'decimal:2',
            'invoice_amount_usd' => 'decimal:2',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FranchisePayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function paidTotal(): float
    {
        return round((float) $this->payments()->sum('amount_paid_usd'), 2);
    }

    /**
     * Pagado - facturado: > 0 a favor del franquiciado, < 0 lo que debe.
     */
    public function balance(): float
    {
        return round($this->paidTotal() - (float) $this->invoice_amount_usd, 2);
    }
}
