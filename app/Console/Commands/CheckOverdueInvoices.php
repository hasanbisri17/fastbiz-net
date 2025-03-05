<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Check for overdue invoices and update their status and IP bindings';

    public function handle()
    {
        $this->info('Checking for overdue invoices...');

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

            $this->info("{$count} invoices marked as overdue");

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to check overdue invoices: ' . $e->getMessage());
            return 1;
        }
    }
}
