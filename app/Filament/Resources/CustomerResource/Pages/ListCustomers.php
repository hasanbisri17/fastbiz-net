<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generate_bulk_invoices')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $customers = \App\Models\Customer::where('status', 'active')
                        ->whereDoesntHave('invoices', function ($query) {
                            $query->whereMonth('invoice_date', now()->month)
                                ->whereYear('invoice_date', now()->year);
                        })
                        ->get();

                    foreach ($customers as $customer) {
                        $customer->invoices()->create([
                            'service_package_id' => $customer->service_package_id,
                            'amount' => $customer->servicePackage->price,
                            'invoice_date' => now(),
                            'due_date' => $customer->due_date,
                        ]);
                    }

                    $this->notify('success', 'Bulk invoices generated successfully');
                })
                ->requiresConfirmation(),
        ];
    }
}
