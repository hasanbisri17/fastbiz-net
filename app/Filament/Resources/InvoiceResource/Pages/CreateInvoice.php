<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Invoice;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['invoice_number'] = Invoice::generateInvoiceNumber();
        
        // If status is paid, set paid_date
        if ($data['status'] === 'paid' && !isset($data['paid_date'])) {
            $data['paid_date'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $invoice = $this->record;

        // If invoice is marked as paid, update customer's due date
        if ($invoice->status === 'paid') {
            $customer = $invoice->customer;
            $customer->update([
                'due_date' => now()->addMonth(),
            ]);
        }
    }
}
