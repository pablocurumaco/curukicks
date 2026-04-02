<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sneakers', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('inventory_number');
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::table('sneakers', function (Blueprint $table) {
            $table->dropIndex(['brand']);
            $table->dropColumn('brand');
        });
    }
};
