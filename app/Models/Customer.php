<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'service_package_id',
        'router_id',
        'ip_address',
        'mac_address',
        'installation_date',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'due_date' => 'date',
    ];

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function ipBinding()
    {
        return $this->hasOne(IpBinding::class, 'ip_address', 'ip_address')
            ->where('router_id', $this->router_id);
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            // Create IP binding if IP address is set
            if ($customer->ip_address) {
                IpBinding::create([
                    'router_id' => $customer->router_id,
                    'mac_address' => $customer->mac_address,
                    'ip_address' => $customer->ip_address,
                    'type' => 'bypassed',
                    'comment' => "Customer: {$customer->name}",
                ]);
            }
        });

        static::updated(function ($customer) {
            // Update IP binding if IP address changed
            if ($customer->ip_address && $customer->wasChanged(['ip_address', 'mac_address'])) {
                $binding = $customer->ipBinding;
                if ($binding) {
                    $binding->update([
                        'mac_address' => $customer->mac_address,
                        'ip_address' => $customer->ip_address,
                    ]);
                } else {
                    IpBinding::create([
                        'router_id' => $customer->router_id,
                        'mac_address' => $customer->mac_address,
                        'ip_address' => $customer->ip_address,
                        'type' => 'bypassed',
                        'comment' => "Customer: {$customer->name}",
                    ]);
                }
            }
        });

        static::deleted(function ($customer) {
            // Delete associated IP binding
            if ($customer->ip_address) {
                $customer->ipBinding?->delete();
            }
        });
    }
}
