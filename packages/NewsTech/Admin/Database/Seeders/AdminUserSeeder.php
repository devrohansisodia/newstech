<?php

namespace NewsTech\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use NewsTech\Admin\Models\AdminUser;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        AdminUser::query()->updateOrCreate(
            ['email' => config('newstech-admin.auth.email')],
            [
                'name' => 'NewsTech Admin',
                'password' => config('newstech-admin.auth.password'),
                'is_active' => true,
            ]
        );
    }
}
