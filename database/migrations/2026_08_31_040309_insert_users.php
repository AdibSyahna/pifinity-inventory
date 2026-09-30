<?php

use App\Models\User;
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
        User::create([
            'name' => 'admin',
            'email' => 'admin@pifinity.id',

            // 'password' => 'admin'
            'password' => '$2y$12$KeNd8NSUmQeNgNlwxlIah.fbg3knPMgBeJRCD7LDHjPGHWMJ.vUYW'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
