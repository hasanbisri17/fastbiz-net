<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\RouterOSService;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'service_package_id',
        'amount',
        'invoice_date',
        'due_date',
        'paid_date',
        'status',
        'payment_method_id',
        'payment_proof',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });

        // When an invoice becomes overdue, change IP binding to regular
        static::updated(function ($invoice) {
            if ($invoice->status === 'overdue' && $invoice->getOriginal('status') !== 'overdue') {
                static::updateCustomerIpBinding($invoice->customer, 'regular');
            }
            
            // When an invoice is paid, change IP binding back to bypassed
            if ($invoice->status === 'paid' && $invoice->getOriginal('status') !== 'paid') {
                static::updateCustomerIpBinding($invoice->customer, 'bypassed');
            }
        });
    }

    protected static function updateCustomerIpBinding($customer, $type)
    {
        if (!$customer || !$customer->ip_address || !$customer->router_id) {
            return;
        }

        try {
            $binding = $customer->ipBinding;
            if (!$binding) {
                return;
            }

            $routerService = app(RouterOSService::class);
            $router = $customer->router;
            
            $routerService->connect($router->host, $router->username, $router->password, $router->port);
            
            // Update in Mikrotik
            if ($binding->mikrotik_id) {
                $routerService->updateIpBinding(
                    $binding->mikrotik_id,
                    $customer->ip_address,
                    $type,
                    $customer->mac_address,
                    "Customer: {$customer->name}",
                    false
                );
            }

            // Update in database
            $binding->update([
                'type' => $type
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to update IP binding type: " . $e->getMessage());
            throw $e;
        }
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $sequence = (int) substr($lastInvoice->invoice_number, -4);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf("%s%s%s%04d", $prefix, $year, $month, $sequence);
    }

    public function markOverdue()
    {
        if ($this->status === 'unpaid' && $this->due_date < now()) {
            $this->update(['status' => 'overdue']);
        }
    }
}
