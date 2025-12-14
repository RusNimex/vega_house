<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'ООО Ромашка и чай',
                'city' => 'Москва',
            ],
            [
                'name' => 'ЗАО Сбербанан',
                'city' => 'Санкт-Петербург',
            ],
            [
                'name' => 'АО Вайбарис',
                'city' => 'Москва',
            ],
            [
                'name' => 'ООО Роялкомандор',
                'city' => 'Екатеринбург',
            ],
            [
                'name' => 'ИП Микуриоза и друзья по парте',
                'city' => 'Новосибирск',
            ],
            [
                'name' => 'ИП Вкусности для гурманов любящих здоровое питание',
                'city' => 'Казань',
            ],
        ];

        $createdCompanies = [];
        foreach ($companies as $company) {
            $createdCompanies[] = Company::firstOrCreate(
                ['name' => $company['name']],
                ['city' => $company['city']]
            );
        }

        // Связываем первого пользователя со всеми компаниями
        $userEmail = env('ADMIN_USER_EMAIL', 'example@email.com');
        $user = User::where('email', $userEmail)->first();
        
        if ($user) {
            $companyIds = collect($createdCompanies)->pluck('id')->toArray();
            
            // Связываем пользователя со всеми компаниями со случайным значением enabled
            $attachData = [];
            foreach ($companyIds as $companyId) {
                $attachData[$companyId] = ['enabled' => (bool) rand(0, 1)];
            }
            $user->companies()->syncWithoutDetaching($attachData);
        }
    }
}

