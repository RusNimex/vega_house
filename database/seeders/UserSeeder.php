<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Первый юзер, обычно админ
     */
    public function run(): void
    {
        $email = env('ADMIN_USER_EMAIL', 'example@email.com');
        $name = env('ADMIN_USER_NAME', 'Фонарев Константин Михайлович');
        $phone = env('ADMIN_USER_PHONE', '+79083054488');
        $password = env('ADMIN_USER_PASSWORD', 'password123');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make($password),
            ]
        );
    }
}

