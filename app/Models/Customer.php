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
                // Create local IP binding record
                $binding = IpBinding::create([
                    'router_id' => $customer->router_id,
                    'mac_address' => $customer->mac_address,
                    'ip_address' => $customer->ip_address,
                    'type' => 'bypassed',
                    'comment' => "Customer: {$customer->name}",
                ]);

                // Sync with Mikrotik
                try {
                    $routerService = app(\App\Services\RouterOSService::class);
                    $router = $customer->router;
                    
                    $routerService->connect($router->host, $router->username, $router->password, $router->port);
                    $response = $routerService->addIpBinding(
                        $customer->ip_address,
                        'bypassed',
                        $customer->mac_address,
                        "Customer: {$customer->name}",
                        false
                    );

                    if (isset($response[0]['.id'])) {
                        $binding->update(['mikrotik_id' => $response[0]['.id']]);
                    }
                } catch (\Exception $e) {
                    // Log the error but don't stop the process
                    \Log::error("Failed to sync IP binding with Mikrotik: " . $e->getMessage());
                }
            }
        });

        static::updated(function ($customer) {
            // Update IP binding if IP address or MAC address changed
            if ($customer->ip_address && $customer->wasChanged(['ip_address', 'mac_address', 'router_id'])) {
                $binding = $customer->ipBinding;
                
                try {
                    $routerService = app(\App\Services\RouterOSService::class);
                    $router = $customer->router;
                    $routerService->connect($router->host, $router->username, $router->password, $router->port);

                    if ($binding) {
                        // Update existing binding
                        $binding->update([
                            'router_id' => $customer->router_id,
                            'mac_address' => $customer->mac_address,
                            'ip_address' => $customer->ip_address,
                        ]);

                        if ($binding->mikrotik_id) {
                            $routerService->updateIpBinding(
                                $binding->mikrotik_id,
                                $customer->ip_address,
                                'bypassed',
                                $customer->mac_address,
                                "Customer: {$customer->name}",
                                false
                            );
                        }
                    } else {
                        // Create new binding
                        $binding = IpBinding::create([
                            'router_id' => $customer->router_id,
                            'mac_address' => $customer->mac_address,
                            'ip_address' => $customer->ip_address,
                            'type' => 'bypassed',
                            'comment' => "Customer: {$customer->name}",
                        ]);

                        $response = $routerService->addIpBinding(
                            $customer->ip_address,
                            'bypassed',
                            $customer->mac_address,
                            "Customer: {$customer->name}",
                            false
                        );

                        if (isset($response[0]['.id'])) {
                            $binding->update(['mikrotik_id' => $response[0]['.id']]);
                        }
                    }
                } catch (\Exception $e) {
                    // Log the error but don't stop the process
                    \Log::error("Failed to sync IP binding with Mikrotik: " . $e->getMessage());
                }
            }
        });

        static::deleted(function ($customer) {
            // Delete associated IP binding
            if ($customer->ip_address) {
                $binding = $customer->ipBinding;
                if ($binding) {
                    try {
                        // Remove from Mikrotik if we have the Mikrotik ID
                        if ($binding->mikrotik_id) {
                            $routerService = app(\App\Services\RouterOSService::class);
                            $router = $customer->router;
                            $routerService->connect($router->host, $router->username, $router->password, $router->port);
                            $routerService->removeIpBinding($binding->mikrotik_id);
                        }
                    } catch (\Exception $e) {
                        // Log the error but don't stop the process
                        \Log::error("Failed to remove IP binding from Mikrotik: " . $e->getMessage());
                    }

                    // Delete local binding record
                    $binding->delete();
                }
            }
        });
    }
}
