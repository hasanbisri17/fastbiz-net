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
                    
                    // Delete associated IP binding if exists
                    if ($customer->ip_address) {
                        $customer->ipBinding?->delete();
                    }
                }),
            Actions\Action::make('generate_invoice')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $customer = $this->record;
                    
                    // Check if invoice already exists for current month
                    $existingInvoice = $customer->invoices()
                        ->whereMonth('invoice_date', now()->month)
                        ->whereYear('invoice_date', now()->year)
                        ->exists();

                    if ($existingInvoice) {
                        Notification::make()
                            ->warning()
                            ->title('Invoice already exists for this month')
                            ->send();
                        return;
                    }

                    $invoice = $customer->invoices()->create([
                        'service_package_id' => $customer->service_package_id,
                        'amount' => $customer->servicePackage->price,
                        'invoice_date' => now(),
                        'due_date' => $customer->due_date,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Invoice generated successfully')
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
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

        // Update IP binding if IP address changed
        if ($customer->wasChanged(['ip_address', 'mac_address', 'router_id'])) {
            if ($customer->ip_address) {
                $binding = $customer->ipBinding;
                if ($binding) {
                    $binding->update([
                        'router_id' => $customer->router_id,
                        'mac_address' => $customer->mac_address,
                        'ip_address' => $customer->ip_address,
                    ]);
                } else {
                    \App\Models\IpBinding::create([
                        'router_id' => $customer->router_id,
                        'mac_address' => $customer->mac_address,
                        'ip_address' => $customer->ip_address,
                        'type' => 'bypassed',
                        'comment' => "Customer: {$customer->name}",
                    ]);
                }
            } else {
                // Remove IP binding if IP address is cleared
                $customer->ipBinding?->delete();
            }
        }
    }
}
