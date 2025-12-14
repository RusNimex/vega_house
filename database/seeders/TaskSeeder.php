<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('Нет компаний в базе. Сначала запустите CompanySeeder.');
            return;
        }
        
        // Получаем компании по имени для привязки задач
        $romashkaCompany = $companies->where('name', 'ООО Ромашка и чай')->first();
        $sberbananCompany = $companies->where('name', 'ЗАО Сбербанан')->first();
        $vaibarisCompany = $companies->where('name', 'АО Вайбарис')->first();
        $royalCompany = $companies->where('name', 'ООО Роялкомандор')->first();
        $mikuriozaCompany = $companies->where('name', 'ИП Микуриоза и друзья по парте')->first();
        $vkysnostiCompany = $companies->where('name', 'ИП Вкусности для гурманов любящих здоровое питание')->first();

        // Создаем тестовые задачи для каждой компании
        $tasks = [
            // ООО Ромашка и чай (Москва)
            [
                'company_id' => $romashkaCompany->id,
                'status' => 'new',
                'description' => 'Осмотр офисного здания по адресу ул. Ленина, 10. Необходимо проверить состояние фасада, кровли и внутренних помещений.',
                'start' => now()->addDays(1)->setTime(9, 0),
                'deadline' => now()->addDays(3)->setTime(18, 0),
                'address' => 'ул. Ленина, 10, Москва',
                'notes' => 'Встреча с представителем компании в 9:00. При себе иметь фотоаппарат и планшет.',
            ],
            [
                'company_id' => $romashkaCompany->id,
                'status' => 'process',
                'description' => 'Осмотр торгового зала. Проверка состояния торгового оборудования, систем освещения и безопасности.',
                'start' => now()->subDays(1)->setTime(10, 0),
                'deadline' => now()->addDays(2)->setTime(19, 0),
                'address' => 'ул. Тверская, 15, Москва',
                'notes' => 'Работа в процессе. Требуется согласование с руководством по некоторым вопросам.',
            ],
            [
                'company_id' => $romashkaCompany->id,
                'status' => 'complete',
                'description' => 'Осмотр складского помещения. Проверка систем хранения и условий хранения товаров.',
                'start' => now()->subDays(10)->setTime(8, 0),
                'deadline' => now()->subDays(7)->setTime(17, 0),
                'address' => 'ул. Складская, 5, Москва',
                'notes' => 'Осмотр завершен. Все документы оформлены.',
            ],

            // ЗАО Сбербанан (Санкт-Петербург)
            [
                'company_id' => $sberbananCompany->id,
                'status' => 'new',
                'description' => 'Осмотр производственного цеха. Проверка оборудования и соответствия нормам безопасности.',
                'start' => now()->addDays(2)->setTime(8, 0),
                'deadline' => now()->addDays(5)->setTime(18, 0),
                'address' => 'пр. Невский, 100, Санкт-Петербург',
                'notes' => 'Необходимо получить пропуск на территорию заранее.',
            ],
            [
                'company_id' => $sberbananCompany->id,
                'status' => 'process',
                'description' => 'Осмотр административного здания. Проверка состояния офисных помещений и систем жизнеобеспечения.',
                'start' => now()->subDays(3)->setTime(9, 0),
                'deadline' => now()->addDays(1)->setTime(18, 0),
                'address' => 'ул. Садовая, 25, Санкт-Петербург',
                'notes' => 'Работа в процессе. Проверка систем вентиляции и кондиционирования.',
            ],
            [
                'company_id' => $sberbananCompany->id,
                'status' => 'break',
                'description' => 'Осмотр складского комплекса. Проверка систем вентиляции, пожарной безопасности и состояния несущих конструкций.',
                'start' => now()->subDays(5)->setTime(10, 0),
                'deadline' => now()->addDays(3)->setTime(20, 0),
                'address' => 'пр. Мира, 25, Санкт-Петербург',
                'notes' => 'Осмотр приостановлен. Ожидается разрешение на доступ к техническим помещениям.',
            ],

            // АО Вайбарис (Москва)
            [
                'company_id' => $vaibarisCompany->id,
                'status' => 'new',
                'description' => 'Осмотр торгового центра. Проверка состояния торговых площадей, систем кондиционирования и безопасности.',
                'start' => now()->addDays(3)->setTime(9, 0),
                'deadline' => now()->addDays(6)->setTime(20, 0),
                'address' => 'ул. Арбат, 20, Москва',
                'notes' => 'Встреча с директором в 9:00. Большой объект, потребуется весь день.',
            ],
            [
                'company_id' => $vaibarisCompany->id,
                'status' => 'complete',
                'description' => 'Осмотр офисного помещения. Проверка состояния кабинетов и переговорных комнат.',
                'start' => now()->subDays(8)->setTime(11, 0),
                'deadline' => now()->subDays(5)->setTime(16, 0),
                'address' => 'ул. Красная площадь, 1, Москва',
                'notes' => 'Осмотр завершен. Все документы оформлены и переданы заказчику.',
            ],
            [
                'company_id' => $vaibarisCompany->id,
                'status' => 'decline',
                'description' => 'Осмотр производственного участка. Проверка оборудования и соответствия нормам безопасности.',
                'start' => now()->subDays(12)->setTime(9, 0),
                'deadline' => now()->subDays(9)->setTime(18, 0),
                'address' => 'ул. Промышленная, 42, Москва',
                'notes' => 'Задача отклонена. Заказчик отменил осмотр по техническим причинам.',
            ],

            // ООО Роялкомандор (Екатеринбург)
            [
                'company_id' => $royalCompany->id,
                'status' => 'new',
                'description' => 'Осмотр жилого комплекса. Проверка состояния подъездов, лифтового оборудования и придомовой территории.',
                'start' => now()->addDays(4)->setTime(10, 0),
                'deadline' => now()->addDays(7)->setTime(19, 0),
                'address' => 'ул. Пушкина, 5, Екатеринбург',
                'notes' => 'Большой объект. Потребуется несколько дней для полного осмотра.',
            ],
            [
                'company_id' => $royalCompany->id,
                'status' => 'process',
                'description' => 'Осмотр торгового павильона. Проверка состояния торгового оборудования и систем безопасности.',
                'start' => now()->subDays(2)->setTime(8, 0),
                'deadline' => now()->addDays(1)->setTime(17, 0),
                'address' => 'пр. Ленина, 50, Екатеринбург',
                'notes' => 'Работа в процессе. Требуется дополнительная проверка электропроводки.',
            ],
            [
                'company_id' => $royalCompany->id,
                'status' => 'complete',
                'description' => 'Осмотр складского помещения. Проверка условий хранения и систем безопасности.',
                'start' => now()->subDays(15)->setTime(9, 0),
                'deadline' => now()->subDays(12)->setTime(18, 0),
                'address' => 'ул. Складская, 10, Екатеринбург',
                'notes' => 'Осмотр завершен. Все документы оформлены.',
            ],

            // ИП Микуриоза и друзья по парте (Новосибирск)
            [
                'company_id' => $mikuriozaCompany->id,
                'status' => 'new',
                'description' => 'Осмотр кафе. Проверка состояния помещений, систем вентиляции и пожарной безопасности.',
                'start' => now()->addDays(5)->setTime(11, 0),
                'deadline' => now()->addDays(8)->setTime(20, 0),
                'address' => 'ул. Советская, 15, Новосибирск',
                'notes' => 'Осмотр в рабочее время. Согласовать время с руководством.',
            ],
            [
                'company_id' => $mikuriozaCompany->id,
                'status' => 'process',
                'description' => 'Осмотр производственного цеха. Проверка оборудования и соответствия нормам безопасности.',
                'start' => now()->subDays(4)->setTime(8, 0),
                'deadline' => now()->addDays(2)->setTime(19, 0),
                'address' => 'ул. Заводская, 30, Новосибирск',
                'notes' => 'Работа в процессе. Большой объем работ.',
            ],
            [
                'company_id' => $mikuriozaCompany->id,
                'status' => 'break',
                'description' => 'Осмотр офисного здания. Проверка состояния офисных помещений и систем жизнеобеспечения.',
                'start' => now()->subDays(6)->setTime(9, 0),
                'deadline' => now()->addDays(1)->setTime(18, 0),
                'address' => 'пр. Карла Маркса, 20, Новосибирск',
                'notes' => 'Осмотр приостановлен. Ожидается разрешение на доступ к некоторым помещениям.',
            ],

            // ИП Вкусности для гурманов любящих здоровое питание (Казань)
            [
                'company_id' => $vkysnostiCompany->id,
                'status' => 'new',
                'description' => 'Осмотр ресторана. Проверка состояния кухни, зала и систем вентиляции.',
                'start' => now()->addDays(6)->setTime(10, 0),
                'deadline' => now()->addDays(9)->setTime(21, 0),
                'address' => 'ул. Баумана, 58, Казань',
                'notes' => 'Осмотр в нерабочее время. Согласовать с руководством.',
            ],
            [
                'company_id' => $vkysnostiCompany->id,
                'status' => 'process',
                'description' => 'Осмотр производственного цеха. Проверка оборудования и условий производства.',
                'start' => now()->subDays(5)->setTime(8, 0),
                'deadline' => now()->addDays(3)->setTime(18, 0),
                'address' => 'ул. Промышленная, 42, Казань',
                'notes' => 'Работа в процессе. Требуется проверка санитарных норм.',
            ],
            [
                'company_id' => $vkysnostiCompany->id,
                'status' => 'complete',
                'description' => 'Осмотр складского помещения. Проверка условий хранения продуктов и систем безопасности.',
                'start' => now()->subDays(20)->setTime(9, 0),
                'deadline' => now()->subDays(17)->setTime(17, 0),
                'address' => 'ул. Складская, 8, Казань',
                'notes' => 'Осмотр завершен. Все документы оформлены и переданы заказчику.',
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create($taskData);
            
            // Добавляем контакты к задаче
            $allContacts = [
                [
                    'name' => 'Иванов Иван Иванович',
                    'phone' => '+79001234567',
                    'email' => 'ivanov@example.com',
                ],
                [
                    'name' => 'Петрова Мария Сергеевна',
                    'phone' => '+79007654321',
                    'email' => 'petrova@example.com',
                ],
                [
                    'name' => 'Сидоров Петр Александрович',
                    'phone' => '+79009876543',
                    'email' => 'sidorov@example.com',
                ],
                [
                    'name' => 'Козлова Анна Викторовна',
                    'phone' => '+79005555555',
                    'email' => 'kozlova@example.com',
                ],
                [
                    'name' => 'Морозов Дмитрий Сергеевич',
                    'phone' => '+79004444444',
                    'email' => 'morozov@example.com',
                ],
                [
                    'name' => 'Волкова Елена Николаевна',
                    'phone' => '+79003333333',
                    'email' => 'volkova@example.com',
                ],
                [
                    'name' => 'Лебедев Андрей Игоревич',
                    'phone' => '+79002222222',
                    'email' => 'lebedev@example.com',
                ],
                [
                    'name' => 'Соколова Ольга Петровна',
                    'phone' => '+79001111111',
                    'email' => 'sokolova@example.com',
                ],
            ];

            // Для каждой задачи добавляем случайное количество контактов (1-3)
            $contactsCount = rand(1, 3);
            $selectedContacts = array_rand($allContacts, min($contactsCount, count($allContacts)));
            
            if (!is_array($selectedContacts)) {
                $selectedContacts = [$selectedContacts];
            }
            
            foreach ($selectedContacts as $contactIndex) {
                Contact::create(array_merge($allContacts[$contactIndex], ['task_id' => $task->id]));
            }
        }

        $this->command->info('Создано ' . count($tasks) . ' тестовых задач с контактами.');
    }
}

