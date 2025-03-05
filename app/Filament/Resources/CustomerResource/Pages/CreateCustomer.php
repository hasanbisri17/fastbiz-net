<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $customer = $this->record;

        // Show notification about IP binding sync
        if ($customer->ip_address) {
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
                    Notification::make()
                        ->success()
                        ->title('IP Binding Created')
                        ->body("IP binding was successfully created in router {$router->name}")
                        ->send();
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('IP Binding Failed')
                    ->body("Failed to create IP binding in router: {$e->getMessage()}")
                    ->send();
            }
        }
    }
}
