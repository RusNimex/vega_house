<?php

namespace Tests\Feature\Task;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;

/**
 * Создание юзера, компании и прочие однотипные вещи
 */
trait TaskTestHelpers
{
    /**
     * Юзер
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ], $attributes));
    }

    /**
     * Компания
     */
    protected function createCompany(array $attributes = []): Company
    {
        return Company::factory()->create($attributes);
    }

    /**
     * Привязывает компанию к пользователю
     */
    protected function attachCompanyToUser(User $user, Company $company, bool $enabled = true): void
    {
        $user->companies()->attach($company->id, ['enabled' => $enabled]);
    }

    /**
     * Юзер с компанией
     */
    protected function createUserWithCompany(array $userAttributes = [], array $companyAttributes = [], bool $companyEnabled = true): array
    {
        $user = $this->createUser($userAttributes);
        $company = $this->createCompany($companyAttributes);
        $this->attachCompanyToUser($user, $company, $companyEnabled);

        return ['user' => $user, 'company' => $company];
    }

    /**
     * Токен юзера
     */
    protected function createTokenForUser(User $user, string $tokenName = 'test-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Задача для компании
     */
    protected function createTaskForCompany(Company $company, array $attributes = []): Task
    {
        return Task::factory()->create(array_merge([
            'company_id' => $company->id,
        ], $attributes));
    }

    /**
     * Авторизация JSON запроса
     */
    protected function authenticatedJson(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = $this->createTokenForUser($user);
        $request = $this->withHeader('Authorization', 'Bearer ' . $token);

        return match (strtoupper($method)) {
            'GET' => $request->getJson($uri),
            'POST' => $request->postJson($uri, $data),
            'PUT' => $request->putJson($uri, $data),
            'PATCH' => $request->patchJson($uri, $data),
            'DELETE' => $request->deleteJson($uri, $data),
            default => $request->json($method, $uri, $data),
        };
    }
}

