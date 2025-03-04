<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use HasFactory;

    public function ipBindings(): HasMany
    {
        return $this->hasMany(IpBinding::class);
    }

    protected $fillable = [
        'name',
        'host',
        'username',
        'password',
        'port',
        'description',
        'status'
    ];

    protected $casts = [
        'port' => 'integer',
        'status' => 'boolean',
    ];
}
