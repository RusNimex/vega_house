<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешного получения задач
     */
    public function test_user_can_get_their_tasks(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'status' => TaskStatus::NEW->value,
        ]);

        Contact::factory()->create(['task_id' => $task->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'company_id',
                        'status',
                        'description',
                        'start',
                        'deadline',
                        'address',
                        'notes',
                        'created_at',
                        'updated_at',
                        'company' => [
                            'id',
                            'name',
                            'city',
                        ],
                        'contacts' => [
                            '*' => [
                                'id',
                                'task_id',
                                'name',
                                'phone',
                                'email',
                            ],
                        ],
                    ],
                ],
                'path',
                'per_page',
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($task->id, $response->json('data.0.id'));
    }

    /**
     * Тест: получает только свои (из своих компаний)
     */
    public function test_user_only_gets_tasks_from_their_companies(): void
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user1->companies()->attach($company1->id, ['enabled' => true]);
        $user2->companies()->attach($company2->id, ['enabled' => true]);

        $task1 = Task::factory()->create(['company_id' => $company1->id]);
        $task2 = Task::factory()->create(['company_id' => $company2->id]);

        $token = $user1->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200);
        $tasks = $response->json('data');

        $this->assertCount(1, $tasks);
        $this->assertEquals($task1->id, $tasks[0]['id']);
        $this->assertNotEquals($task2->id, $tasks[0]['id']);
    }

    /**
     * Тест: без компаний получает пустой список
     */
    public function test_user_without_companies_gets_empty_list(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'per_page',
            ]);

        $this->assertCount(0, $response->json('data'));
    }

    /**
     * Тест: требуется аутентификация
     */
    public function test_tasks_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401);
    }

    /**
     * Тест: пагинация
     */
    public function test_tasks_pagination_works(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        // Создаем 10 задач
        Task::factory()->count(10)->create(['company_id' => $company->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Запрашиваем с per_page=3
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks?per_page=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'per_page',
                'path',
            ]);

        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(3, $response->json('per_page'));
    }

    /**
     * Тест: валидация per_page (максимум 100)
     */
    public function test_per_page_maximum_is_100(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Тест: валидация per_page (минимум 1)
     */
    public function test_per_page_minimum_is_1(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Тест: задачи сортируются по created_at desc
     */
    public function test_tasks_are_sorted_by_created_at_desc(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'created_at' => now()->subDays(2),
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'created_at' => now()->subDays(1),
        ]);

        $task3 = Task::factory()->create([
            'company_id' => $company->id,
            'created_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200);
        $tasks = $response->json('data');

        $this->assertCount(3, $tasks);
        // Самая новая задача должна быть первой
        $this->assertEquals($task3->id, $tasks[0]['id']);
        $this->assertEquals($task2->id, $tasks[1]['id']);
        $this->assertEquals($task1->id, $tasks[2]['id']);
    }

    /**
     * Тест: инфa о компании
     */
    public function test_tasks_include_company_information(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create([
            'name' => 'Test Company',
            'city' => 'Moscow',
        ]);
        $user->companies()->attach($company->id, ['enabled' => true]);

        Task::factory()->create(['company_id' => $company->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200);
        $task = $response->json('data.0');

        $this->assertArrayHasKey('company', $task);
        $this->assertEquals($company->id, $task['company']['id']);
        $this->assertEquals('Test Company', $task['company']['name']);
        $this->assertEquals('Moscow', $task['company']['city']);
    }

    /**
     * Тест: контакты
     */
    public function test_tasks_include_contacts(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task = Task::factory()->create(['company_id' => $company->id]);
        $contact1 = Contact::factory()->create(['task_id' => $task->id]);
        $contact2 = Contact::factory()->create(['task_id' => $task->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200);
        $taskData = $response->json('data.0');

        $this->assertArrayHasKey('contacts', $taskData);
        $this->assertCount(2, $taskData['contacts']);
        $this->assertEquals($contact1->id, $taskData['contacts'][0]['id']);
        $this->assertEquals($contact2->id, $taskData['contacts'][1]['id']);
    }
}

