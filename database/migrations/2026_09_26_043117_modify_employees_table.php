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
        Schema::table('employees', function (Blueprint $table) {
            // modify column 'status' to be enum with values '0' and '1'
            $table->enum('status', ['0', '1'])->default('1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // revert back to original column definition
            // which is a string with default value of 'active'
            $table->string('status')->default('active')->change();
        });
    }
};
