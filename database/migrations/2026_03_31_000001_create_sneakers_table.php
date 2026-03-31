<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sneakers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('inventory_number')->unique();
            $table->string('model');
            $table->string('colorway');
            $table->string('style_code')->nullable();
            $table->string('size');
            $table->string('condition'); // enum: DS, Used
            $table->boolean('has_box')->default(false);
            $table->string('store')->nullable();
            $table->unsignedInteger('cost_paid')->nullable();
            $table->unsignedInteger('stockx_price_usd')->nullable();
            $table->date('stockx_checked_at')->nullable();
            $table->unsignedTinyInteger('usd_multiplier')->default(11);
            $table->unsignedInteger('sale_price_gt')->nullable();
            $table->string('decision'); // enum: VENTA, POSIBLE VENTA, etc.
            $table->string('stockx_url')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('status')->default('available'); // enum: available, sold
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index('decision');
            $table->index('status');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sneakers');
    }
};
