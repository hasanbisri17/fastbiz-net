<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Invoice;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('mark_overdue')
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->action(function () {
                    $overdueCount = Invoice::where('status', 'unpaid')
                        ->where('due_date', '<', now())
                        ->update(['status' => 'overdue']);

                    $this->notify('success', $overdueCount . ' invoices marked as overdue');
                })
                ->requiresConfirmation()
                ->modalHeading('Mark Overdue Invoices')
                ->modalDescription('This will mark all unpaid invoices past their due date as overdue.')
                ->modalSubmitActionLabel('Mark Overdue'),
        ];
    }
}
