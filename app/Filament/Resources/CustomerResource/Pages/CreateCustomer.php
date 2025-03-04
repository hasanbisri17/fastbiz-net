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

        // Generate first invoice if customer is active
        if ($customer->status === 'active') {
            $customer->invoices()->create([
                'service_package_id' => $customer->service_package_id,
                'amount' => $customer->servicePackage->price,
                'invoice_date' => now(),
                'due_date' => $customer->due_date,
            ]);

            Notification::make()
                ->success()
                ->title('Initial invoice generated')
                ->send();
        }
    }
}
