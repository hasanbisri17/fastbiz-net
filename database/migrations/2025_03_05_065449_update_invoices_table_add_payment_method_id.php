<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Remove old payment_method column
            $table->dropColumn('payment_method');
            
            // Add new payment_method_id column
            $table->foreignId('payment_method_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Remove the foreign key and column
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
            
            // Add back the old column
            $table->string('payment_method')->nullable();
        });
    }
};
