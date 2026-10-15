<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Login accounts for every Mind OS role.
 *
 * - Idempotent and non-destructive: an existing account (matched by e-mail)
 *   is never overwritten, so re-running the seeder on a live server will not
 *   reset a password someone has already changed.
 * - The initial password comes from SEED_DEFAULT_PASSWORD in .env; set it to
 *   something private before seeding production, then change each account's
 *   password after the first login.
 *
 * Run alone with: php artisan db:seed --class=AccountSeeder --force
 */
class AccountSeeder extends Seeder
{
    /**
     * [role, name, email, username, phone]
     *
     * @return list<array{0:string,1:string,2:string,3:string,4:string}>
     */
    public static function accounts(): array
    {
        return [
            ['Superadmin', 'Super Admin Mind OS', 'superadmin@mindos.mooda.id', 'mindos.superadmin', '081200000001'],
            ['admin', 'Admin Operasional', 'admin@mindos.mooda.id', 'mindos.admin', '081200000002'],
            ['Practitioner', 'Practitioner Mind OS', 'practitioner@mindos.mooda.id', 'mindos.practitioner', '081200000003'],
            ['Practitioner', 'Master Practitioner Mind OS', 'master@mindos.mooda.id', 'mindos.master', '081200000004'],
            ['Trainer', 'Trainer Mind OS', 'trainer@mindos.mooda.id', 'mindos.trainer', '081200000005'],
            ['Klien', 'Klien Demo', 'klien@mindos.mooda.id', 'mindos.klien', '081200000006'],
            ['Klien', 'Klien Demo Dua', 'klien2@mindos.mooda.id', 'mindos.klien2', '081200000007'],
        ];
    }

    public static function defaultPassword(): string
    {
        // Read through config so it still works after `php artisan config:cache`.
        $password = (string) config('security.seed_default_password');

        if ($password !== '') {
            return $password;
        }

        // The fallback is public (this repository is public), so it must never
        // become a real password on a live server.
        if (app()->isProduction()) {
            throw new \RuntimeException('Set SEED_DEFAULT_PASSWORD in .env (then run `php artisan optimize`) before seeding accounts in production.');
        }

        return 'MindOs@2026!';
    }

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rows = [];

        foreach (self::accounts() as [$role, $name, $email, $username, $phone]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'no_wa' => $phone,
                    'password' => self::defaultPassword(), // hashed by the model cast
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            if (! $user->hasRole($roleModel)) {
                $user->assignRole($roleModel);
            }

            $rows[] = [$role, $email, $user->wasRecentlyCreated ? 'dibuat' : 'sudah ada (tidak diubah)'];
        }

        $this->command?->table(['Role', 'Email', 'Status'], $rows);
        $this->command?->warn('Password awal akun baru: nilai SEED_DEFAULT_PASSWORD di .env. Segera ganti setelah login pertama.');
    }
}
