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
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('consignment_agents');
            // agreement number
            $table->string('agreement_number')->unique();

            $table->enum('status', ['draft', 'active', 'overdue', 'finished'])
                ->default('draft')
                ->comment('overdue = reached end date, finished = after delivery');

            // start & end date
            $table->integer('duration_days')->default(1);
            $table->date('start_date')->nullable();
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignments');
    }
};
