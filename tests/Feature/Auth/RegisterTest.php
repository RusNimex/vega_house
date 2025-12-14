<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешной регистрации пользователя
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
        ]);

        $user = User::where('email', 'ivan@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('password123', $user->password); // Пароль должен быть захеширован
    }

    /**
     * Тест валидации: имя обязательно
     */
    public function test_registration_requires_name(): void
    {
        $userData = [
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Тест валидации: телефон обязателен
     */
    public function test_registration_requires_phone(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * Тест валидации: email обязателен
     */
    public function test_registration_requires_email(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации: email должен быть валидным
     */
    public function test_registration_requires_valid_email(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации: email должен быть уникальным
     */
    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации: пароль обязателен
     */
    public function test_registration_requires_password(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест валидации: пароль должен быть минимум 8 символов
     */
    public function test_registration_requires_password_min_length(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест валидации: пароль должен быть подтвержден
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест: после регистрации пользователь может использовать токен
     */
    public function test_registered_user_can_use_token(): void
    {
        $userData = [
            'name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201);
        $token = $response->json('access_token');

        // Проверяем, что токен работает для получения информации о пользователе
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/user');

        $meResponse->assertStatus(200)
            ->assertJson([
                'email' => 'ivan@example.com',
                'name' => 'Иван Иванов',
                'phone' => '+79991234567',
            ]);
    }
}

