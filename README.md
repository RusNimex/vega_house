# Vega House API

REST API для мобильного приложения - осмотр объектов недвижимости и хранение данных по ним.

## Технологии

- **Laravel 11** - PHP фреймворк
- **MySQL 8** - База данных
- **Laravel Sanctum** - Аутентификация для API

## Использование API

### Базовый URL

```
http://localhost:8000/api
```

### Аутентификация

API использует Laravel Sanctum для аутентификации через токены.

#### Регистрация

```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "Иван Иванов",
    "phone": "+79991234567",
    "email": "ivan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Ответ:**
```json
{
    "user": {
        "id": 1,
        "name": "Иван Иванов",
        "email": "ivan@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
}
```

#### Вход

```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "ivan@example.com",
    "password": "password123"
}
```

**Ответ:** Аналогичен ответу регистрации.

#### Получение информации о текущем пользователе

```http
GET /api/v1/user
Authorization: Bearer {access_token}
```

**Ответ:**
```json
{
    "id": 1,
    "name": "Иван Иванов",
    "phone": "+79991234567",
    "email": "ivan@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

#### Обновление профиля пользователя

```http
PUT /api/v1/profile/update
Authorization: Bearer {access_token}
Content-Type: application/json

{
    "name": "Иван Петров",
    "phone": "+79997654321",
    "email": "ivan.new@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Примечание:** Все поля опциональны. Можно обновлять только те поля, которые необходимо изменить. При обновлении пароля обязательно требуется поле `password_confirmation`, которое должно совпадать с `password`.

**Ответ:**
```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "Иван Петров",
        "phone": "+79997654321",
        "email": "ivan.new@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### Получение компаний пользователя

```http
GET /api/v1/profile/company
Authorization: Bearer {access_token}
```

**Ответ:**
```json
{
    "companies": [
        {
            "id": 1,
            "name": "ООО Ромашка и чай",
            "city": "Москва",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "pivot": {
                "user_id": 1,
                "company_id": 1,
                "enabled": 1,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        }
    ]
}
```

**Примечание:** Возвращает все компании, связанные с текущим пользователем. В поле `pivot` содержится информация о связи между пользователем и компанией, включая флаг `enabled`.

#### Выход

```http
POST /api/v1/logout
Authorization: Bearer {access_token}
```

> **Примечание:** Информация о развертывании, установке и командах Sail находится в файле [DEPLOY_local.md](DEPLOY_local.md).

## Лицензия

Приложение является тестовым. Развернул для собственного развития, пощупать новую версию лары в связке с sanctum, чтоб понимать возможности библиотеки и что у нее внутри.
