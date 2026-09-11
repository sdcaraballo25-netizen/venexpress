<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverRemunerationRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount_usd',
        'effective_at',
        'source',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'effective_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Última tarifa registrada (la vigente).
     */
    public static function current(): ?self
    {
        return static::query()
            ->orderByDesc('effective_at')
            ->orderByDesc('id')
            ->first();
    }
}
