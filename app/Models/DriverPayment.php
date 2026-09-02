<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pendiente';
    public const STATUS_PAID = 'pagada';
    public const STATUS_CANCELLED = 'cancelada';

    protected $fillable = [
        'driver_id',
        'package_id',
        'amount_usd',
        'status',
        'paid_at',
        'paid_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo { return $this->belongsTo(Driver::class); }
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function paidBy(): BelongsTo { return $this->belongsTo(User::class, 'paid_by_user_id'); }
}
