<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_doc',
        'user_id',
        'name',
        'phone',
        'email',
    ];

    protected $casts = [
        'id_doc' => 'string',
        'name' => 'string',
        'phone' => 'string',
        'email' => 'string',
    ];

    /**
     * Cuenta de cliente que demostró ser dueña de esta cédula
     * registrándose con ella (ver register.blade.php). Solo esa
     * cuenta puede fijar/liberar este vínculo — nunca se establece
     * desde el flujo de un aliado (PackageCreate::syncCustomer()).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
