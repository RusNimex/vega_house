<?php

namespace Tests\Feature\Task;

use App\Enums\SubtaskStatus;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Task;
use App\Models\TasksObject;
use App\Models\TasksSubtask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: успешное получение задачи по ID
     */
    public function test_user_can_get_task_by_id(): void
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

        $contact = Contact::factory()->create(['task_id' => $task->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'company_id',
                    'status',
                    'description',
                    'start',
                    'deadline',
                    'address',
                    'notes',
                    'contacts',
                    'objects_amount',
                    'objects_completed',
                ]
            ]);

        $this->assertEquals($task->id, $response->json('data.id'));
        $this->assertCount(1, $response->json('data.contacts'));
        $this->assertEquals($contact->id, $response->json('data.contacts.0.id'));
    }

    /**
     * Тест: задача не найдена (404)
     */
    public function test_get_task_returns_404_when_not_found(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/task/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Task not found']);
    }

    /**
     * Тест: задача принадлежит другой компании (404)
     */
    public function test_user_cannot_get_task_from_other_company(): void
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

        $task = Task::factory()->create([
            'company_id' => $company2->id,
        ]);

        $token = $user1->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Task not found']);
    }

    /**
     * Тест: требуется аутентификация
     */
    public function test_get_task_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/task/1');

        $response->assertStatus(401);
    }

    /**
     * Тест: задача включает контакты
     */
    public function test_task_includes_contacts(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
        ]);

        $contact1 = Contact::factory()->create(['task_id' => $task->id]);
        $contact2 = Contact::factory()->create(['task_id' => $task->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(200);
        $contacts = $response->json('data.contacts');

        $this->assertCount(2, $contacts);
        $this->assertEquals($contact1->id, $contacts[0]['id']);
        $this->assertEquals($contact2->id, $contacts[1]['id']);
    }

    /**
     * Тест: задача включает статистику подзадач
     */
    public function test_task_includes_subtasks_statistics(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
        ]);

        // Создаем подзадачи с разными статусами
        TasksObject::create([
            'task_id' => $task->id,
            'name' => 'Дом 1',
            'completed' => 1,
        ]);

        TasksObject::create([
            'task_id' => $task->id,
            'name' => 'Дом 2',
            'completed' => 0,
        ]);

        TasksObject::create([
            'task_id' => $task->id,
            'name' => 'Дом 3',
            'completed' => 0,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(200);

        $this->assertEquals(3, $response->json('data.objects_amount'));
        $this->assertEquals(1, $response->json('data.objects_completed'));
    }

    /**
     * Тест: статистика подзадач равна 0, если подзадач нет
     */
    public function test_task_subtasks_statistics_are_zero_when_no_subtasks(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['enabled' => true]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(200);

        $this->assertEquals(0, $response->json('data.objects_amount'));
        $this->assertEquals(0, $response->json('data.objects_completed'));
    }

    /**
     * Тест: пользователь без активных компаний не может получить задачу
     */
    public function test_user_without_active_companies_cannot_get_task(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();
        // Привязываем компанию, но с enabled = false
        $user->companies()->attach($company->id, ['enabled' => false]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/task/{$task->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Task not found']);
    }
}

