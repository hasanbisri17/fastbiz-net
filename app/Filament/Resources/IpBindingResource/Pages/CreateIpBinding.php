<?php

namespace App\Filament\Resources\IpBindingResource\Pages;

use App\Filament\Resources\IpBindingResource;
use App\Services\RouterOSService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateIpBinding extends CreateRecord
{
    protected static string $resource = IpBindingResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $router = $record->router;

        try {
            $routerService = app(RouterOSService::class);
            $routerService->connect($router->host, $router->username, $router->password, $router->port);
            
            $response = $routerService->addIpBinding(
                $record->ip_address,
                $record->type,
                $record->mac_address,
                $record->comment,
                $record->disabled
            );

            if (isset($response[0]['.id'])) {
                $record->update(['mikrotik_id' => $response[0]['.id']]);
            }

            Notification::make()
                ->success()
                ->title('IP Binding created successfully')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to create IP Binding on router')
                ->body($e->getMessage())
                ->send();
        }
    }
}
