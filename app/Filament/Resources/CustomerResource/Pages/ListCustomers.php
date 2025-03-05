<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generateInvoices')
                ->label('Generate Invoices')
                ->icon('heroicon-o-document-plus')
                ->form([
                    DatePicker::make('invoice_month')
                        ->label('Invoice Month')
                        ->format('Y-m')
                        ->displayFormat('F Y')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data): void {
                    $month = Carbon::parse($data['invoice_month'])->format('Y-m');
                    $startDate = Carbon::parse($month)->startOfMonth();
                    $endDate = Carbon::parse($month)->endOfMonth();
                    
                    // Get all active customers
                    $customers = $this->getModel()::where('status', 'active')->get();
                    $generated = 0;
                    $skipped = 0;
                    
                    foreach ($customers as $customer) {
                        // Check if invoice already exists for this month
                        $existingInvoice = Invoice::where('customer_id', $customer->id)
                            ->whereBetween('invoice_date', [$startDate, $endDate])
                            ->exists();
                            
                        if ($existingInvoice) {
                            $skipped++;
                            continue;
                        }
                        
                        // Generate new invoice
                        Invoice::create([
                            'customer_id' => $customer->id,
                            'service_package_id' => $customer->service_package_id,
                            'amount' => $customer->servicePackage->price,
                            'invoice_date' => $startDate,
                            'due_date' => $customer->due_date ? Carbon::parse($startDate)->setDay($customer->due_date->day) : $endDate,
                            'status' => 'unpaid',
                        ]);
                        
                        $generated++;
                    }
                    
                    Notification::make()
                        ->title('Invoices Generated')
                        ->body("Successfully generated {$generated} invoices. {$skipped} customers skipped (already have invoices).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
