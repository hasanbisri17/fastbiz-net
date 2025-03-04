<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('mark_as_paid')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $invoice = $this->record;
                    
                    $invoice->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ]);

                    // Update customer's due date
                    $invoice->customer->update([
                        'due_date' => now()->addMonth(),
                    ]);

                    $this->notify('success', 'Invoice marked as paid');
                })
                ->visible(fn ($record) => $record->status === 'unpaid' || $record->status === 'overdue'),

            Actions\Action::make('print')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('invoice.print', $record))
                ->openUrlInNewTab(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If status is changed to paid, set paid_date
        if ($data['status'] === 'paid' && $this->record->status !== 'paid' && !isset($data['paid_date'])) {
            $data['paid_date'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $invoice = $this->record;

        // If invoice is marked as paid, update customer's due date
        if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
            $invoice->customer->update([
                'due_date' => now()->addMonth(),
            ]);
        }
    }
}
