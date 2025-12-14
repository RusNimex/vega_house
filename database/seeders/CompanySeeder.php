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

        // Случайная связь пользователей с компаниями (только первые 10 для оптимизации)
        $users = User::take(10)->get();
        $companyIds = collect($createdCompanies)->pluck('id')->toArray();

        foreach ($users as $user) {
            // Случайное количество компаний для пользователя (от 0 до всех)
            $randomCount = rand(0, count($companyIds));
            
            if ($randomCount > 0) {
                // Случайный выбор компаний
                $randomCompanyIds = collect($companyIds)
                    ->shuffle()
                    ->take($randomCount)
                    ->toArray();

                // Связываем пользователя с компаниями (без удаления существующих связей)
                $attachData = [];
                foreach ($randomCompanyIds as $companyId) {
                    $attachData[$companyId] = ['enabled' => (bool) rand(0, 1)];
                }
                $user->companies()->syncWithoutDetaching($attachData);
            }
        }
    }
}

