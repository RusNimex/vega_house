<?php

namespace Tests\Feature\Profile;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Task\TaskTestHelpers;
use Tests\TestCase;

class ProfileOptionTest extends TestCase
{
    use RefreshDatabase, TaskTestHelpers;

    /**
     * Тест успешного обновления опции по option_id
     */
    public function test_user_can_update_option_by_id(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
            'description' => 'Загружать данные только при подключении к Wi-Fi сети',
        ]);

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'option_id' => $option->id,
            'value' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'option' => [
                    'id',
                    'key',
                    'name',
                    'description',
                    'pivot' => [
                        'user_id',
                        'option_id',
                        'value',
                    ],
                ],
            ])
            ->assertJson([
                'message' => 'Option updated successfully',
                'option' => [
                    'id' => $option->id,
                    'key' => 'upload_if_wifi_online',
                ],
            ]);

        $this->assertDatabaseHas('user_options', [
            'user_id' => $user->id,
            'option_id' => $option->id,
            'value' => 1,
        ]);
    }

    /**
     * Тест успешного обновления опции по key
     */
    public function test_user_can_update_option_by_key(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'save_photo_in_phone',
            'name' => 'Сохранять фото на телефоне',
            'description' => 'Сохранять фотографии в галерею телефона',
        ]);

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'key' => 'save_photo_in_phone',
            'value' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Option updated successfully',
                'option' => [
                    'key' => 'save_photo_in_phone',
                ],
            ]);

        $this->assertDatabaseHas('user_options', [
            'user_id' => $user->id,
            'option_id' => $option->id,
            'value' => 0,
        ]);
    }

    /**
     * Тест обновления опции с false значением
     */
    public function test_user_can_set_option_to_false(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'touch_id_face_id_enabled',
            'name' => 'Использовать Touch ID / Face ID',
            'description' => 'Использовать биометрическую аутентификацию',
        ]);

        // Сначала устанавливаем значение true
        $user->options()->attach($option->id, ['value' => 1]);

        // Затем обновляем на false
        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'option_id' => $option->id,
            'value' => false,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_options', [
            'user_id' => $user->id,
            'option_id' => $option->id,
            'value' => 0,
        ]);
    }

    /**
     * Тест валидации: требуется option_id или key
     */
    public function test_update_option_requires_option_id_or_key(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'value' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['option_id', 'key']);
    }

    /**
     * Тест валидации: требуется value
     */
    public function test_update_option_requires_value(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
        ]);

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'option_id' => $option->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    /**
     * Тест валидации: option_id должен существовать
     */
    public function test_update_option_fails_with_nonexistent_option_id(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'option_id' => 99999,
            'value' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['option_id']);
    }

    /**
     * Тест валидации: key должен существовать
     */
    public function test_update_option_fails_with_nonexistent_key(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'key' => 'nonexistent_key',
            'value' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    /**
     * Тест: требуется аутентификация
     */
    public function test_update_option_requires_authentication(): void
    {
        $option = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
        ]);

        $response = $this->putJson('/api/v1/profile/options', [
            'option_id' => $option->id,
            'value' => true,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест: создание новой связи если её не было
     */
    public function test_update_option_creates_relation_if_not_exists(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
        ]);

        // Проверяем, что связи нет
        $this->assertDatabaseMissing('user_options', [
            'user_id' => $user->id,
            'option_id' => $option->id,
        ]);

        $response = $this->authenticatedJson($user, 'PUT', '/api/v1/profile/options', [
            'option_id' => $option->id,
            'value' => true,
        ]);

        $response->assertStatus(200);

        // Проверяем, что связь создана
        $this->assertDatabaseHas('user_options', [
            'user_id' => $user->id,
            'option_id' => $option->id,
            'value' => 1,
        ]);
    }

    /**
     * Тест успешного получения всех опций без установленных значений
     */
    public function test_user_can_get_all_options_with_default_values(): void
    {
        $user = $this->createUser();

        $option1 = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
            'description' => 'Загружать данные только при подключении к Wi-Fi сети',
        ]);

        $option2 = Option::create([
            'key' => 'save_photo_in_phone',
            'name' => 'Сохранять фото на телефоне',
            'description' => 'Сохранять фотографии в галерею телефона',
        ]);

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/profile/options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'options' => [
                    '*' => [
                        'id',
                        'key',
                        'name',
                        'description',
                        'value',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'options')
            ->assertJson([
                'options' => [
                    [
                        'id' => $option1->id,
                        'key' => 'upload_if_wifi_online',
                        'name' => 'Загружать только при Wi-Fi',
                        'value' => false,
                    ],
                    [
                        'id' => $option2->id,
                        'key' => 'save_photo_in_phone',
                        'name' => 'Сохранять фото на телефоне',
                        'value' => false,
                    ],
                ],
            ]);
    }

    /**
     * Тест получения опций с установленными значениями
     */
    public function test_user_can_get_options_with_set_values(): void
    {
        $user = $this->createUser();

        $option1 = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
            'description' => 'Загружать данные только при подключении к Wi-Fi сети',
        ]);

        $option2 = Option::create([
            'key' => 'save_photo_in_phone',
            'name' => 'Сохранять фото на телефоне',
            'description' => 'Сохранять фотографии в галерею телефона',
        ]);

        $option3 = Option::create([
            'key' => 'touch_id_face_id_enabled',
            'name' => 'Использовать Touch ID / Face ID',
            'description' => 'Использовать биометрическую аутентификацию',
        ]);

        // Устанавливаем значения для некоторых опций
        $user->options()->attach($option1->id, ['value' => 1]);
        $user->options()->attach($option2->id, ['value' => 0]);
        // option3 не устанавливаем

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/profile/options');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'options')
            ->assertJson([
                'options' => [
                    [
                        'id' => $option1->id,
                        'key' => 'upload_if_wifi_online',
                        'value' => true,
                    ],
                    [
                        'id' => $option2->id,
                        'key' => 'save_photo_in_phone',
                        'value' => false,
                    ],
                    [
                        'id' => $option3->id,
                        'key' => 'touch_id_face_id_enabled',
                        'value' => false, // По умолчанию false, так как не установлено
                    ],
                ],
            ]);
    }

    /**
     * Тест получения опций: требуется аутентификация
     */
    public function test_get_options_requires_authentication(): void
    {
        Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
        ]);

        $response = $this->getJson('/api/v1/profile/options');

        $response->assertStatus(401);
    }

    /**
     * Тест получения опций: пустой список если опций нет
     */
    public function test_user_can_get_empty_options_list(): void
    {
        $user = $this->createUser();

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/profile/options');

        $response->assertStatus(200)
            ->assertJson([
                'options' => [],
            ])
            ->assertJsonCount(0, 'options');
    }

    /**
     * Тест получения опций: проверка структуры ответа
     */
    public function test_get_options_returns_correct_structure(): void
    {
        $user = $this->createUser();

        $option = Option::create([
            'key' => 'upload_if_wifi_online',
            'name' => 'Загружать только при Wi-Fi',
            'description' => 'Загружать данные только при подключении к Wi-Fi сети',
        ]);

        $user->options()->attach($option->id, ['value' => 1]);

        $response = $this->authenticatedJson($user, 'GET', '/api/v1/profile/options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'options' => [
                    '*' => [
                        'id',
                        'key',
                        'name',
                        'description',
                        'value',
                    ],
                ],
            ]);

        $options = $response->json('options');
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('key', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
        $this->assertArrayHasKey('description', $options[0]);
        $this->assertArrayHasKey('value', $options[0]);
        $this->assertIsBool($options[0]['value']);
    }
}

