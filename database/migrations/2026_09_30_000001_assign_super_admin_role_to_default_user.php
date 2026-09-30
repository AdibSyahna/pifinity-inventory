<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleName = config('filament-shield.super_admin.name', 'super_admin');
        $guardName = config('auth.defaults.guard', 'web');

        // Ensure the super admin role exists (idempotent).
        $role = Role::findOrCreate($roleName, $guardName);

        // Assign it to the default user seeded by 2026_08_31_040309_insert_users.
        $user = User::where('email', 'admin@pifinity.id')->first();
        if ($user && ! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        // Clear Spatie permission cache so Gate::before sees the new role immediately.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roleName = config('filament-shield.super_admin.name', 'super_admin');

        $user = User::where('email', 'admin@pifinity.id')->first();

        if ($user && $user->hasRole($roleName)) {
            $user->removeRole($roleName);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
