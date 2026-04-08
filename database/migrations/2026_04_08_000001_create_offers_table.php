<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sneaker_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sneaker_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
