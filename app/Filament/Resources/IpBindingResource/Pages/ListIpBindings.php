<?php

namespace App\Filament\Resources\IpBindingResource\Pages;

use App\Filament\Resources\IpBindingResource;
use App\Models\Router;
use App\Services\RouterOSService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListIpBindings extends ListRecords
{
    protected static string $resource = IpBindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('sync')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Select::make('router_id')
                        ->label('Router')
                        ->options(Router::pluck('name', 'id'))
                        ->required()
                ])
                ->action(function (array $data): void {
                    $router = Router::findOrFail($data['router_id']);
                    $routerService = app(RouterOSService::class);

                    try {
                        $results = $routerService->syncIpBindings($router);
                        
                        $notification = Notification::make()
                            ->success()
                            ->title('IP Bindings Synchronized')
                            ->body("Created: {$results['created']}, Updated: {$results['updated']}, Removed: {$results['removed']}");
                        
                        if (!empty($results['errors'])) {
                            $notification->warning()
                                ->body("Sync completed with some errors:\n" . implode("\n", $results['errors']));
                        }
                        
                        $notification->send();
                        
                        $this->refresh();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Sync Failed')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
