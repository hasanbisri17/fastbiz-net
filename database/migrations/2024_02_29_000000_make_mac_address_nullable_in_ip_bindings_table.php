<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_bindings', function (Blueprint $table) {
            $table->string('mac_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ip_bindings', function (Blueprint $table) {
            $table->string('mac_address')->nullable(false)->change();
        });
    }
};
