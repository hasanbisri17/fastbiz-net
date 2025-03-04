<?php

namespace App\Filament\Resources\IpBindingResource\Pages;

use App\Filament\Resources\IpBindingResource;
use App\Services\RouterOSService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditIpBinding extends EditRecord
{
    protected static string $resource = IpBindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $record = $this->record;
                    $router = $record->router;

                    try {
                        if ($record->mikrotik_id) {
                            $routerService = app(RouterOSService::class);
                            $routerService->connect($router->host, $router->username, $router->password, $router->port);
                            $routerService->removeIpBinding($record->mikrotik_id);
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->warning()
                            ->title('Warning')
                            ->body('Failed to remove IP Binding from router: ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $router = $record->router;

        try {
            $routerService = app(RouterOSService::class);
            $routerService->connect($router->host, $router->username, $router->password, $router->port);

            if ($record->mikrotik_id) {
                $routerService->updateIpBinding(
                    $record->mikrotik_id,
                    $record->ip_address,
                    $record->type,
                    $record->mac_address,
                    $record->comment,
                    $record->disabled
                );
            } else {
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
            }

            Notification::make()
                ->success()
                ->title('IP Binding updated successfully')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to update IP Binding on router')
                ->body($e->getMessage())
                ->send();
        }
    }
}
