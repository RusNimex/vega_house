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
POST /api/register
Content-Type: application/json

{
    "name": "Иван Иванов",
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
POST /api/login
Content-Type: application/json

{
    "email": "ivan@example.com",
    "password": "password123"
}
```

**Ответ:** Аналогичен ответу регистрации.

#### Получение информации о текущем пользователе

```http
GET /api/user
Authorization: Bearer {access_token}
```

#### Выход

```http
POST /api/logout
Authorization: Bearer {access_token}
```

> **Примечание:** Информация о развертывании, установке и командах Sail находится в файле [DEPLOY_local.md](DEPLOY_local.md).

## Лицензия

Приложение является тестовым. Развернул для собственного развития, пощупать новую версию лары в связке с sanctum, чтоб понимать возможности библиотеки и что у нее внутри.
