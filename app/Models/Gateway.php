<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = [
        'name',
        'secret_key',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
