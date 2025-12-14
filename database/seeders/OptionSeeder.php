<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\User;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Настройки профиля
     */
    public function run(): void
    {
        $options = [
            [
                'key' => 'upload_if_wifi_online',
                'name' => 'Выгружать осмотр только при Wi-Fi соединении',
                'description' => 'Загружать данные только при подключении к Wi-Fi сети',
            ],
            [
                'key' => 'save_photo_in_phone',
                'name' => 'Дублировать все фотографии в галереи телефона',
                'description' => 'Сохранять фотографии в галерею телефона',
            ],
            [
                'key' => 'touch_id_face_id_enabled',
                'name' => 'Touch ID/Face ID и код пароль',
                'description' => 'Использовать биометрическую аутентификацию',
            ],
        ];

        $createdOptions = [];
        foreach ($options as $option) {
            $createdOptions[] = Option::firstOrCreate(
                ['key' => $option['key']],
                [
                    'name' => $option['name'],
                    'description' => $option['description'],
                ]
            );
        }

        // Связываем первого пользователя со всеми опциями (все включены)
        $userEmail = env('ADMIN_USER_EMAIL', 'example@email.com');
        $user = User::where('email', $userEmail)->first();
        
        if ($user) {
            $optionIds = collect($createdOptions)->pluck('id')->toArray();
            
            // Связываем пользователя со всеми опциями со значением true
            $attachData = [];
            foreach ($optionIds as $optionId) {
                $attachData[$optionId] = ['value' => 1];
            }
            $user->options()->syncWithoutDetaching($attachData);
        }
    }
}

