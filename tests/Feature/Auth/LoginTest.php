<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест входа
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'ivan@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'access_token',
                'token_type',
            ])
            ->assertJson([
                'user' => [
                    'email' => 'ivan@example.com',
                ],
                'token_type' => 'Bearer',
            ]);

        $this->assertNotNull($response->json('access_token'));
    }

    /**
     * Тест валидации: mail обязателен
     */
    public function test_login_requires_email(): void
    {
        $loginData = [
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации: mail должен быть валидным
     */
    public function test_login_requires_valid_email(): void
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации: пас обязателен
     */
    public function test_login_requires_password(): void
    {
        $loginData = [
            'email' => 'ivan@example.com',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест: неверный mail
     */
    public function test_login_fails_with_incorrect_email(): void
    {
        User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    /**
     * Тест: неверный пас
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'ivan@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    /**
     * Тест: несуществующий
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    /**
     * Тест: после входа юзер может использовать токен
     */
    public function test_logged_in_user_can_use_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'ivan@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200);
        $token = $response->json('access_token');

        // Проверяем, что токен работает
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/user');

        $meResponse->assertStatus(200)
            ->assertJson([
                'email' => 'ivan@example.com',
                'id' => $user->id,
            ]);
    }

    /**
     * Тест: разные юзеры получают разные токены
     */
    public function test_different_users_get_different_tokens(): void
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response1 = $this->postJson('/api/v1/login', [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);

        $response2 = $this->postJson('/api/v1/login', [
            'email' => 'user2@example.com',
            'password' => 'password123',
        ]);

        $response1->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user1->id,
                    'email' => 'user1@example.com',
                ],
            ]);

        $response2->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user2->id,
                    'email' => 'user2@example.com',
                ],
            ]);

        $token1 = $response1->json('access_token');
        $token2 = $response2->json('access_token');

        $this->assertNotNull($token1);
        $this->assertNotNull($token2);
        $this->assertNotEquals($token1, $token2);

        // Проверяем, что каждый токен работает только для своего юзера
        // Сбрасываем перед запросом
        $this->actingAs($user1);
        $me1Response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/v1/user');

        $this->actingAs($user2);
        $me2Response = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/v1/user');

        $me1Response->assertStatus(200);
        $me2Response->assertStatus(200);

        $me1 = $me1Response->json();
        $me2 = $me2Response->json();

        $this->assertEquals($user1->id, $me1['id'], 'First token should return first user');
        $this->assertEquals($user2->id, $me2['id'], 'Second token should return second user');
        $this->assertNotEquals($me1['id'], $me2['id'], 'Users should be different');
    }
}

