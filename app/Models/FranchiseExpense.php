<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gasto operativo de una franquicia (ver Ally::isFranchise()). Solo lo
 * registra/elimina el Franquiciado dueño, desde Ally\FranchiseFinance.
 */
class FranchiseExpense extends Model
{
    public const CATEGORIES = [
        'alquiler' => 'Alquiler',
        'nomina' => 'Nómina / personal',
        'servicios' => 'Servicios (luz, agua, internet)',
        'insumos' => 'Insumos y empaques',
        'transporte' => 'Transporte',
        'impuestos' => 'Impuestos y trámites',
        'publicidad' => 'Publicidad',
        'mantenimiento' => 'Mantenimiento',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'ally_id',
        'created_by_user_id',
        'category',
        'description',
        'amount_usd',
        'spent_on',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
