<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        DB::table('users')->insertOrIgnore([
            'email' => $email,
            'name' => 'Super Admin',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
            'account_type' => 'admin',
            'account_status' => 'active',
            'locale' => 'en',
            'theme' => 'system',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = (int) DB::table('users')->where('email', $email)->value('id');
        $roleId = (int) DB::table('roles')->where('name', 'super_admin')->value('id');
        if (! DB::table('user_roles')->where('user_id', $userId)->where('role_id', $roleId)->whereNull('company_id')->exists()) {
            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
                'company_id' => null,
                'assigned_by' => null,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
