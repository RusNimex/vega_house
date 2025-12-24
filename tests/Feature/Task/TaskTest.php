<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\Contact;
use App\Models\TasksObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase, TaskTestHelpers;

    /**
     * Тест: успешное получение задачи по ID
     */
    public function test_user_can_get_task_by_id(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company, [
            'status' => TaskStatus::NEW->value,
        ]);

        $contact = Contact::factory()->create(['task_id' => $task->id]);

        $response = $this->authenticatedJson($user, 'GET', "/api/v1/task/{$task->id}");

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
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/task/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Resource not found']);
    }

    /**
     * Тест: задача принадлежит другой компании (404)
     */
    public function test_user_cannot_get_task_from_other_company(): void
    {
        ['user' => $user1, 'company' => $company1] = $this->createUserWithCompany(['email' => 'user1@example.com']);
        ['user' => $user2, 'company' => $company2] = $this->createUserWithCompany(['email' => 'user2@example.com']);

        $task = $this->createTaskForCompany($company2);

        $response = $this->authenticatedJson($user1, 'GET', "/api/v1/task/{$task->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Resource not found']);
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
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company);

        $contact1 = Contact::factory()->create(['task_id' => $task->id]);
        $contact2 = Contact::factory()->create(['task_id' => $task->id]);

        $response = $this->authenticatedJson($user, 'GET', "/api/v1/task/{$task->id}");

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
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company);

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

        $response = $this->authenticatedJson($user, 'GET', "/api/v1/task/{$task->id}");

        $response->assertStatus(200);

        $this->assertEquals(3, $response->json('data.objects_amount'));
        $this->assertEquals(1, $response->json('data.objects_completed'));
    }

    /**
     * Тест: статистика подзадач равна 0, если подзадач нет
     */
    public function test_task_subtasks_statistics_are_zero_when_no_subtasks(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company);

        $response = $this->authenticatedJson($user, 'GET', "/api/v1/task/{$task->id}");

        $response->assertStatus(200);

        $this->assertEquals(0, $response->json('data.objects_amount'));
        $this->assertEquals(0, $response->json('data.objects_completed'));
    }

    /**
     * Тест: пользователь без активных компаний не может получить задачу
     */
    public function test_user_without_active_companies_cannot_get_task(): void
    {
        $user = $this->createUser();
        $company = $this->createCompany();
        // Привязываем компанию, но с enabled = false
        $this->attachCompanyToUser($user, $company, false);

        $task = $this->createTaskForCompany($company);

        $response = $this->authenticatedJson($user, 'GET', "/api/v1/task/{$task->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Resource not found']);
    }

    /**
     * Тест: сохраняем заметку к задаче
     */
    public function test_save_user_notes_for_task(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company);

        $response = $this->authenticatedJson($user, 'PUT', "/api/v1/task/{$task->id}", [
            'notes' => 'test_save_user_notes_for_task test_save_user_notes_for_task'
        ]);

        $response->assertStatus(200);
    }

    /**
     * Тест: кривой текст заметки
     */
    public function test_save_user_notes_fail_for_task(): void
    {
        ['user' => $user, 'company' => $company] = $this->createUserWithCompany();

        $task = $this->createTaskForCompany($company);

        $response = $this->authenticatedJson($user, 'PUT', "/api/v1/task/{$task->id}", [
            'notes' => '    '
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('notes');
    }
}

