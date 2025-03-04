<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpBinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'mac_address',
        'ip_address',
        'type',
        'comment',
        'disabled',
        'mikrotik_id',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
