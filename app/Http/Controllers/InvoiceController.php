<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function print(Invoice $invoice)
    {
        return view('invoices.print', [
            'invoice' => $invoice->load(['customer', 'servicePackage']),
        ]);
    }
}
