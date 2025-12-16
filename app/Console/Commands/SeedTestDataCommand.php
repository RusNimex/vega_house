<?php

namespace App\Console\Commands;

use Database\Seeders\CompanySeeder;
use Database\Seeders\OptionSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class SeedTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-test {--force : Загрузить без подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить все тестовые данные: пользователя, компании, задачи и контакты';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Загрузить все тестовые данные? Это создаст пользователя, компании, задачи и контакты.')) {
            $this->info('Операция отменена.');
            return Command::FAILURE;
        }

        $this->info('Начинаем загрузку тестовых данных...');
        $this->newLine();

        // Загружаем опции (базовые данные)
        $this->info('Загрузка опций...');
        $this->call('db:seed', ['--class' => OptionSeeder::class]);
        $this->newLine();

        // Загружаем тестовые данные
        $this->info('Загрузка пользователя...');
        $this->call('db:seed', ['--class' => UserSeeder::class]);
        $this->newLine();

        $this->info('Загрузка компаний...');
        $this->call('db:seed', ['--class' => CompanySeeder::class]);
        $this->newLine();

        $this->info('Загрузка задач и контактов...');
        $this->call('db:seed', ['--class' => TaskSeeder::class]);
        $this->newLine();

        $this->info('✅ Все тестовые данные успешно загружены!');
        
        return Command::SUCCESS;
    }
}

