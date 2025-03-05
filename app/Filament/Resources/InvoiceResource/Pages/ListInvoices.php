<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Invoice;
use Filament\Notifications\Notification;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('mark_overdue')
                ->label('Mark overdue')
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->action(function () {
                    try {
                        $count = 0;
                        $errors = [];

                        Invoice::where('status', 'unpaid')
                            ->where('due_date', '<', now())
                            ->chunk(100, function ($invoices) use (&$count, &$errors) {
                                foreach ($invoices as $invoice) {
                                    try {
                                        $invoice->update(['status' => 'overdue']);
                                        $count++;
                                    } catch (\Exception $e) {
                                        $errors[] = "Failed to update invoice #{$invoice->invoice_number}: {$e->getMessage()}";
                                    }
                                }
                            });

                        if ($count > 0) {
                            Notification::make()
                                ->title('Success')
                                ->body("{$count} invoices marked as overdue")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Information')
                                ->body('No unpaid invoices found past their due date')
                                ->info()
                                ->send();
                        }

                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Warning')
                                ->body(implode("\n", $errors))
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to mark invoices as overdue: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Mark Overdue Invoices')
                ->modalDescription('This will mark all unpaid invoices past their due date as overdue. This will also change their IP Binding type to "regular" in Mikrotik.')
                ->modalSubmitActionLabel('Mark Overdue'),
        ];
    }
}
