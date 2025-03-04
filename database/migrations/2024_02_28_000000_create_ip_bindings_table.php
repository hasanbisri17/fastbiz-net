<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->onDelete('cascade');
            $table->string('mac_address');
            $table->string('ip_address');
            $table->string('type')->default('bypassed');
            $table->string('comment')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('mikrotik_id')->nullable(); // To store Mikrotik's .id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_bindings');
    }
};
