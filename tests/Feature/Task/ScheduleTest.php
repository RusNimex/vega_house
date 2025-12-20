<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\Contact;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase, TaskTestHelpers;

    /**
     * Тест успешного получения задач
     */
    public function test_user_can_get_their_tasks(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company, [
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDay(), // Дата в прошлом, чтобы прошла фильтрацию
        ]);

        Contact::factory()->create(['task_id' => $task->id]);

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule');

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
                    ],
                ],
                'meta' => [
                    'per_page',
                ],
                'links',
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($task->id, $response->json('data.0.id'));
    }

    /**
     * Тест: получает только свои (из своих компаний)
     */
    public function test_user_only_gets_tasks_from_their_companies(): void
    {
        ['user' => $user1, 'company' => $company1] = $this->createUserWithCompany(['email' => 'user1@example.com']);
        ['user' => $user2, 'company' => $company2] = $this->createUserWithCompany(['email' => 'user2@example.com']);

        $task1 = $this->createTaskForCompany($company1, [
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDay(),
        ]);
        $task2 = $this->createTaskForCompany($company2, [
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDay(),
        ]);

        $response = $this->authenticatedJson($user1, 'GET', '/api/v1/schedule');

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
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['per_page'],
                'links',
            ]);

        $this->assertCount(0, $response->json('data'));
    }

    /**
     * Тест: требуется аутентификация
     */
    public function test_tasks_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/schedule');

        $response->assertStatus(401);
    }

    /**
     * Тест: пагинация
     */
    public function test_tasks_pagination_works(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        // Создаем 10 задач
        Task::factory()->count(10)->create([
            'company_id' => $company->id,
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDay(),
        ]);

        // Запрашиваем с per_page=3
        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule?per_page=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'path',
                    'per_page',
                    'next_cursor',
                    'prev_cursor',
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(3, $response->json('meta.per_page'));
    }

    /**
     * Тест: валидация per_page (максимум 100)
     */
    public function test_per_page_maximum_is_100(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Тест: валидация per_page (минимум 1)
     */
    public function test_per_page_minimum_is_1(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Тест: задачи сортируются по created_at desc
     */
    public function test_tasks_are_sorted_by_created_at_desc(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task1 = $this->createTaskForCompany($company, [
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDays(2),
            'created_at' => now()->subDays(2),
        ]);

        $task2 = $this->createTaskForCompany($company, [
            'status' => TaskStatus::NEW->value,
            'start' => now()->subDays(1),
            'created_at' => now()->subDays(1),
        ]);

        $task3 = $this->createTaskForCompany($company, [
            'status' => TaskStatus::NEW->value,
            'start' => now(),
            'created_at' => now(),
        ]);

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/schedule');

        $response->assertStatus(200);
        $tasks = $response->json('data');

        $this->assertCount(3, $tasks);
        // Самая новая задача должна быть первой
        $this->assertEquals($task3->id, $tasks[0]['id']);
        $this->assertEquals($task2->id, $tasks[1]['id']);
        $this->assertEquals($task1->id, $tasks[2]['id']);
    }
}

