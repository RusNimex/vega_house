<?php

namespace Tests\Feature\Profile;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileCompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешного обновления состояния на enabled = true
     */
    public function test_user_can_enable_company(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create([
            'name' => 'ООО Ромашка и чай',
            'city' => 'Москва',
        ]);

        // Связываем компанию с юзером и enabled = false
        $user->companies()->attach($company->id, ['enabled' => false]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
                'enabled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'company' => [
                    'id',
                    'name',
                    'city',
                    'pivot' => [
                        'user_id',
                        'company_id',
                        'enabled',
                    ],
                ],
            ])
            ->assertJson([
                'message' => 'Company status updated successfully',
                'company' => [
                    'id' => $company->id,
                    'name' => 'ООО Ромашка и чай',
                    'city' => 'Москва',
                ],
            ]);

        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'enabled' => 1,
        ]);
    }

    /**
     * Тест успешного обновления состояния на enabled = false
     */
    public function test_user_can_disable_company(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create([
            'name' => 'ООО Тестовая компания',
            'city' => 'Санкт-Петербург',
        ]);

        // Связываем компанию с юзером и enabled = true
        $user->companies()->attach($company->id, ['enabled' => true]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
                'enabled' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Company status updated successfully',
                'company' => [
                    'id' => $company->id,
                ],
            ]);

        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'enabled' => 0,
        ]);
    }

    /**
     * Тест валидации: требуется company_id
     */
    public function test_update_company_requires_company_id(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'enabled' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    /**
     * Тест валидации: требуется enabled
     */
    public function test_update_company_requires_enabled(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['enabled']);
    }

    /**
     * Тест валидации: company_id должна быть
     */
    public function test_update_company_fails_with_nonexistent_company_id(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => 99999,
                'enabled' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    /**
     * Тест: компания долнжа принадлежать юзеру
     */
    public function test_update_company_fails_if_company_not_belongs_to_user(): void
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();

        // Компания принадлежит только user2
        $user2->companies()->attach($company->id, ['enabled' => true]);

        $token = $user1->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
                'enabled' => false,
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Company not found or does not belong to user',
            ]);
    }

    /**
     * Тест: аутентификация
     */
    public function test_update_company_requires_authentication(): void
    {
        $company = Company::factory()->create();

        $response = $this->putJson('/api/v1/profile/company', [
            'company_id' => $company->id,
            'enabled' => true,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест: обновление несколько раз подряд
     */
    public function test_user_can_toggle_company_status_multiple_times(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $company = Company::factory()->create();

        $user->companies()->attach($company->id, ['enabled' => true]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Первое обновление: true -> false
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
                'enabled' => false,
            ]);

        $response1->assertStatus(200);
        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'enabled' => 0,
        ]);

        // Второе обновление: false -> true
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/profile/company', [
                'company_id' => $company->id,
                'enabled' => true,
            ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'enabled' => 1,
        ]);
    }

}

