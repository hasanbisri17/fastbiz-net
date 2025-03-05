<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $customer = $this->record;
                    
                    // Show notification about IP binding removal
                    if ($customer->ip_address) {
                        $binding = $customer->ipBinding;
                        if ($binding && $binding->mikrotik_id) {
                            try {
                                $routerService = app(\App\Services\RouterOSService::class);
                                $router = $customer->router;
                                $routerService->connect($router->host, $router->username, $router->password, $router->port);
                                $routerService->removeIpBinding($binding->mikrotik_id);

                                Notification::make()
                                    ->success()
                                    ->title('IP Binding Removed')
                                    ->body("IP binding was successfully removed from router {$router->name}")
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->warning()
                                    ->title('IP Binding Removal Warning')
                                    ->body("Failed to remove IP binding from router: {$e->getMessage()}")
                                    ->send();
                            }
                        }
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $customer = $this->record;

        // Show notification about IP binding sync if IP or MAC changed
        if ($customer->wasChanged(['ip_address', 'mac_address', 'router_id'])) {
            try {
                $routerService = app(\App\Services\RouterOSService::class);
                $router = $customer->router;
                $routerService->connect($router->host, $router->username, $router->password, $router->port);

                $binding = $customer->ipBinding;
                if ($binding && $binding->mikrotik_id) {
                    // Update existing binding in Mikrotik
                    $routerService->updateIpBinding(
                        $binding->mikrotik_id,
                        $customer->ip_address,
                        'bypassed',
                        $customer->mac_address,
                        "Customer: {$customer->name}",
                        false
                    );

                    Notification::make()
                        ->success()
                        ->title('IP Binding Updated')
                        ->body("IP binding was successfully updated in router {$router->name}")
                        ->send();
                } else {
                    // Create new binding in Mikrotik
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
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('IP Binding Failed')
                    ->body("Failed to sync IP binding with router: {$e->getMessage()}")
                    ->send();
            }
        }

        // Update customer's due date if status changed to active
        if ($customer->wasChanged('status') && $customer->status === 'active') {
            $customer->update([
                'due_date' => now()->addMonth(),
            ]);

            Notification::make()
                ->success()
                ->title('Due Date Updated')
                ->body('Customer due date has been extended by one month.')
                ->send();
        }
    }
}
