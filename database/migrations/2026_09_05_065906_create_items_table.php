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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('color_id')->constrained('colors');
            $table->foreignId('size_id')->constrained('sizes');
            $table->text('description')->nullable();
            $table->integer('weight')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price_item', 10, 2)->comment("Base price of the item");
            $table->decimal('price_acc', 10, 2)->comment("Accessories price");
            $table->decimal('price_ship', 10, 2)->comment("Shipping price");
            $table->decimal('price_min_profit', 10, 2)->comment("Minimum profit")->nullable();
            $table->decimal('price_max_profit', 10, 2)->comment("Target profit");
            // $table->decimal('price_tax', 10, 2)->comment("Tax");
            // $table->decimal('price_round', 10, 2)->comment("Rounding");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
