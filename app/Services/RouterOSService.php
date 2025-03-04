<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class RouterOSService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = config('routeros');
    }

    public function testConnection($host, $user, $pass, $port = 8728): bool
    {
        try {
            $client = new Client([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => $port,
            ]);
            
            // Try to get system identity to verify connection
            $response = $client->query('/system/identity/print')->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function connect($host, $user, $pass, $port = 8728)
    {
        try {
            $this->client = new Client([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => $port,
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
    }

    public function query($command)
    {
        if (!$this->client) {
            throw new \Exception('Not connected to router');
        }
        return $this->client->query($command);
    }

    public function getIdentity()
    {
        try {
            $response = $this->client->query('/system/identity/print')->read();
            return $response[0]['name'] ?? null;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get identity: ' . $e->getMessage());
        }
    }

    // IP Pool Methods
    public function getIpPools()
    {
        try {
            if (!$this->client) {
                throw new \Exception('Not connected to router');
            }
            $response = $this->client->query('/ip/pool/print')->read();
            return array_map(function($pool) {
                return [
                    'name' => $pool['name'] ?? '',
                    'ranges' => $pool['ranges'] ?? '',
                    '.id' => $pool['.id'] ?? '',
                ];
            }, $response);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get IP pools: ' . $e->getMessage());
        }
    }

    public function addIpPool($name, $ranges)
    {
        try {
            if (!$this->client) {
                throw new \Exception('Not connected to router');
            }
            return $this->client->query([
                '/ip/pool/add',
                '=name=' . $name,
                '=ranges=' . $ranges,
            ])->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to add IP pool: ' . $e->getMessage());
        }
    }

    public function removeIpPool($id)
    {
        try {
            if (!$this->client) {
                throw new \Exception('Not connected to router');
            }
            return $this->client->query([
                '/ip/pool/remove',
                '=.id=' . $id,
            ])->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to remove IP pool: ' . $e->getMessage());
        }
    }

    // IP Binding Methods
    public function getIpBindings()
    {
        try {
            return $this->client->query('/ip/hotspot/ip-binding/print')->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get IP bindings: ' . $e->getMessage());
        }
    }

    public function addIpBinding($ipAddress, $type = 'bypassed', $macAddress = null, $comment = null, $disabled = false)
    {
        try {
            $query = [
                '/ip/hotspot/ip-binding/add',
                '=address=' . $ipAddress,
                '=type=' . $type,
            ];

            if ($macAddress) {
                $query[] = '=mac-address=' . $macAddress;
            }

            if ($comment) {
                $query[] = '=comment=' . $comment;
            }

            if ($disabled) {
                $query[] = '=disabled=yes';
            }

            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to add IP binding: ' . $e->getMessage());
        }
    }

    public function updateIpBinding($id, $ipAddress, $type = 'bypassed', $macAddress = null, $comment = null, $disabled = false)
    {
        try {
            $query = [
                '/ip/hotspot/ip-binding/set',
                '=.id=' . $id,
                '=address=' . $ipAddress,
                '=type=' . $type,
            ];

            if ($macAddress !== null) {
                $query[] = '=mac-address=' . $macAddress;
            }

            if ($comment !== null) {
                $query[] = '=comment=' . $comment;
            }

            if ($disabled !== null) {
                $query[] = '=disabled=' . ($disabled ? 'yes' : 'no');
            }

            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to update IP binding: ' . $e->getMessage());
        }
    }

    public function removeIpBinding($id)
    {
        try {
            return $this->client->query([
                '/ip/hotspot/ip-binding/remove',
                '=.id=' . $id,
            ])->read();
        } catch (\Exception $e) {
            throw new \Exception('Failed to remove IP binding: ' . $e->getMessage());
        }
    }

    public function syncIpBindings($router)
    {
        try {
            // Connect to the router
            $this->connect($router->host, $router->username, $router->password, $router->port);

            // Get IP bindings from Mikrotik
            $mikrotikBindings = $this->getIpBindings();

            $results = [
                'created' => 0,
                'updated' => 0,
                'removed' => 0,
                'errors' => [],
            ];

            // Get existing bindings for this router from database
            $existingBindings = \App\Models\IpBinding::where('router_id', $router->id)->get();
            $existingBindingsByMikrotikId = $existingBindings->keyBy('mikrotik_id');

            // Process Mikrotik bindings
            foreach ($mikrotikBindings as $binding) {
                try {
                    $data = [
                        'router_id' => $router->id,
                        'mac_address' => $binding['mac-address'] ?? null,
                        'ip_address' => $binding['address'] ?? '',
                        'type' => $binding['type'] ?? 'regular',
                        'comment' => $binding['comment'] ?? '',
                        'disabled' => ($binding['disabled'] ?? 'false') === 'true',
                        'mikrotik_id' => $binding['.id'],
                    ];

                    if (isset($existingBindingsByMikrotikId[$binding['.id']])) {
                        // Update existing binding
                        $existingBindingsByMikrotikId[$binding['.id']]->update($data);
                        $results['updated']++;
                    } else {
                        // Create new binding
                        \App\Models\IpBinding::create($data);
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing binding {$binding['.id']}: " . $e->getMessage();
                }
            }

            // Remove bindings that no longer exist in Mikrotik
            $mikrotikIds = collect($mikrotikBindings)->pluck('.id')->toArray();
            $removedCount = \App\Models\IpBinding::where('router_id', $router->id)
                ->whereNotIn('mikrotik_id', $mikrotikIds)
                ->delete();
            $results['removed'] = $removedCount;

            return $results;
        } catch (\Exception $e) {
            throw new \Exception('Failed to sync IP bindings: ' . $e->getMessage());
        }
    }
}
