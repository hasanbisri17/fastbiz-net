<?php

namespace App\Filament\Resources\IpPoolResource\Pages;

use App\Filament\Resources\IpPoolResource;
use App\Models\Router;
use App\Services\RouterOSService;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ManagePools extends Page
{
    protected static string $resource = IpPoolResource::class;

    protected static string $view = 'filament.resources.ip-pool-resource.pages.manage-pools';

    public array $pools = [];
    public Router $record;

    public function mount(Router $record): void
    {
        $this->record = $record;
        $this->loadPools();
    }

    public function getTitle(): string
    {
        return "IP Pools on {$this->record->name}";
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add IP Pool')
                ->modalHeading('Create IP Pool')
                ->modalSubmitActionLabel('Create')
                ->color('success') // Green color for Add button
                ->form([
                    TextInput::make('name')
                        ->label('Pool Name')
                        ->required(),
                    TextInput::make('ranges')
                        ->label('IP Ranges')
                        ->placeholder('192.168.1.2-192.168.1.254')
                        ->required()
                        ->helperText('You can specify multiple ranges separated by commas'),
                ])
                ->action(function (array $data): void {
                    $this->createPool($data);
                }),
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-m-arrow-path')
                ->color('info') // Blue color for Refresh button
                ->action(fn () => $this->loadPools()),
        ];
    }

    public function loadPools(): void
    {
        $service = app(RouterOSService::class);

        try {
            $service->connect($this->record->host, $this->record->username, $this->record->password, $this->record->port);
            $this->pools = $service->getIpPools();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function createPool(array $data): void
    {
        $service = app(RouterOSService::class);

        try {
            $ranges = collect(explode(',', $data['ranges']))
                ->map(fn ($range) => trim($range))
                ->filter()
                ->toArray();

            $service->connect($this->record->host, $this->record->username, $this->record->password, $this->record->port);
            $service->addIpPool($data['name'], implode(',', $ranges));
            $this->loadPools();

            Notification::make()
                ->title('Success')
                ->body('IP Pool created successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deletePool(string $name): void
    {
        $service = app(RouterOSService::class);

        try {
            $service->connect($this->record->host, $this->record->username, $this->record->password, $this->record->port);
            $service->removeIpPool($name);
            $this->loadPools();

            Notification::make()
                ->title('Success')
                ->body('IP Pool deleted successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
