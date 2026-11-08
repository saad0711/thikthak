<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Local test accounts. Every one uses the password "password".
 * Admins can only be created here, never through the sign-up form.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['ThikThak Admin', 'admin@thikthak.test', '01700000001', UserRole::Admin, 'Gulshan'],
            ['Demo Customer', 'customer@thikthak.test', '01700000002', UserRole::Customer, 'Dhanmondi'],
            ['Demo Provider', 'provider@thikthak.test', '01700000003', UserRole::Provider, 'Mirpur'],
        ];

        foreach ($accounts as [$name, $email, $phone, $role, $area]) {
            $user = User::where('email', $email)->first() ?? new User(['email' => $email]);

            $user->fill(['name' => $name, 'phone' => $phone, 'area' => $area, 'password' => 'password']);
            $user->role = $role;
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->save();

            if ($user->isProvider()) {
                $user->providerProfile()->firstOrCreate();
            }
        }
    }
}
