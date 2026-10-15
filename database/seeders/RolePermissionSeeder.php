<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ================================================
        // 1. CREATE ALL PERMISSIONS
        // ================================================

        // --- Navigation / Page Access ---
        $navPermissions = [
            'view_dashboard',
            'view_data_master',
            'view_resources',
            'view_help',
        ];

        // --- Granular User Management ---
        $userPermissions = [
            'user.show',
            'user.create',
            'user.edit',
            'user.delete',
            'user.massdelete',
            'user.ban',
        ];

        // --- Granular Role Management ---
        $rolePermissions = [
            'role.show',
            'role.create',
            'role.edit',
            'role.delete',
            'role.massdelete',
        ];

        // Create all permissions
        // --- Mind OS ---
        $mindOsPermissions = [
            'mindos.manage',   // kelola konten, layanan, booking & sertifikasi
            'mindos.practice', // praktisi: menangani sesi klien berbayar
        ];

        $allPermissions = array_merge(
            $navPermissions, $userPermissions, $rolePermissions, $mindOsPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ================================================
        // 2. CREATE ROLES (if they don't exist)
        // ================================================
        $roleSuperadmin = Role::firstOrCreate(['name' => 'Superadmin']);
        $roleAdmin      = Role::firstOrCreate(['name' => 'admin']);
        $rolePractitioner = Role::firstOrCreate(['name' => 'Practitioner']);
        $roleTrainer      = Role::firstOrCreate(['name' => 'Trainer']);
        $roleKlien        = Role::firstOrCreate(['name' => 'Klien']);

        // ================================================
        // 3. ASSIGN PERMISSIONS TO ROLES
        // ================================================

        // SUPERADMIN — gets everything implicitly via Gate::before in AppServiceProvider
        // But we still assign explicitly for completeness
        $roleSuperadmin->syncPermissions(Permission::all());

        // ADMIN — All except Resources (User/Role Management)
        $adminPermissions = [
            'view_dashboard',
            'view_data_master',
            'view_help',
        ];
        $roleAdmin->syncPermissions(array_merge($adminPermissions, ['mindos.manage']));

        $rolePractitioner->syncPermissions(['view_dashboard', 'mindos.practice']);
        $roleTrainer->syncPermissions(['view_dashboard', 'mindos.practice']);
        $roleKlien->syncPermissions(['view_dashboard']);
    }
}
