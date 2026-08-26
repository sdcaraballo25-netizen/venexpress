<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_doc',
        'name',
        'phone',
    ];

    protected $casts = [
        'id_doc' => 'string',
        'name' => 'string',
        'phone' => 'string',
    ];
}
