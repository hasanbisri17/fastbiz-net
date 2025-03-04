<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            margin: 0;
            color: #333;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px;
        }
        .invoice-details .label {
            font-weight: bold;
            width: 150px;
        }
        .invoice-items {
            margin-bottom: 30px;
        }
        .invoice-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-items th, .invoice-items td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .invoice-items th {
            background-color: #f5f5f5;
        }
        .invoice-total {
            text-align: right;
            margin-top: 20px;
        }
        .invoice-total .amount {
            font-size: 1.2em;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>INVOICE</h1>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td class="label">Invoice Number:</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td class="label">Date:</td>
                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Due Date:</td>
                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                <td class="label">Status:</td>
                <td>{{ ucfirst($invoice->status) }}</td>
            </tr>
        </table>
    </div>

    <div class="invoice-details">
        <h3>Bill To:</h3>
        <table>
            <tr>
                <td class="label">Customer Name:</td>
                <td>{{ $invoice->customer->name }}</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td>{{ $invoice->customer->address }}</td>
            </tr>
            <tr>
                <td class="label">Phone:</td>
                <td>{{ $invoice->customer->phone }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $invoice->customer->email }}</td>
            </tr>
        </table>
    </div>

    <div class="invoice-items">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Internet Service - {{ $invoice->servicePackage->name }}<br>
                        <small>{{ $invoice->servicePackage->speed }}</small>
                    </td>
                    <td>Rp {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-total">
            <p>Total Amount: <span class="amount">Rp {{ number_format($invoice->amount, 2) }}</span></p>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        @if($invoice->notes)
            <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>

    <button class="no-print" onclick="window.print()">Print Invoice</button>
</body>
</html>
