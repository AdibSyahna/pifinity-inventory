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
        Schema::create('consignment_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('item_units');
            $table->foreignId('storage_id')->constrained('storages');
            $table->foreignId('consignment_id')->constrained('consignments');
            $table->decimal('price_original', 10, 2)->comment("Original price");
            $table->decimal('profit_original', 10, 2)->comment("Original profit");
            $table->decimal('commission', 10, 2)->comment("Commission per sale");
            $table->enum('status', ['draft', 'active', 'sold', 'returned'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_units');
    }
};
