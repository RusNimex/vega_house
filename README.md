# Vega House API

REST API для мобильного приложения - осмотр объектов недвижимости и хранение данных по ним.

## Технологии

- **Laravel 11** - PHP фреймворк
- **MySQL 8** - База данных
- **Laravel Sanctum** - Аутентификация для API

## Требования

- Docker и Docker Compose
- Git

> **Примечание:** Проект использует Laravel Sail для Docker-окружения. Все зависимости (PHP, MySQL, Redis) запускаются в контейнерах.

## Установка

### 1. Клонирование и установка зависимостей

```bash
composer install
```

### 2. Настройка окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Настройки для Sail уже предустановлены в `.env.example`. При необходимости отредактируйте файл `.env`.

### 3. Запуск Laravel Sail

Запустите Docker-окружение:

```bash
./vendor/bin/sail up -d
```

Или создайте alias для удобства (добавьте в `~/.bashrc` или `~/.zshrc`):

```bash
alias sail='./vendor/bin/sail'
```

После этого можно использовать просто `sail up -d`.

### 4. Генерация ключа приложения

```bash
./vendor/bin/sail artisan key:generate
```

### 5. Выполнение миграций

```bash
./vendor/bin/sail artisan migrate
```

### 6. Доступ к приложению

После запуска Sail приложение будет доступно по адресу:

```
http://localhost
```

> **Примечание:** По умолчанию Sail использует порт 80. Если нужно изменить порт, отредактируйте `APP_PORT` в `.env` файле.

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

## Структура проекта

```
vega_house/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # Контроллер аутентификации
│   │   │   └── Controller.php
│   │   └── Middleware/                # Middleware
│   ├── Models/
│   │   └── User.php                   # Модель пользователя
│   └── Providers/                     # Service Providers
├── config/
│   ├── auth.php                       # Конфигурация аутентификации
│   ├── cors.php                       # CORS настройки
│   ├── database.php                   # Конфигурация БД
│   └── sanctum.php                    # Конфигурация Sanctum
├── database/
│   └── migrations/                    # Миграции БД
├── routes/
│   ├── api.php                        # API маршруты
│   └── web.php                        # Web маршруты
├── docker-compose.yml                 # Laravel Sail конфигурация
├── docker/
│   └── mysql/
│       └── data/                      # Данные MySQL (на хосте)
└── .env                               # Переменные окружения
```

## Разработка с Laravel Sail

### Основные команды Sail

```bash
# Запуск контейнеров
./vendor/bin/sail up -d

# Остановка контейнеров
./vendor/bin/sail down

# Просмотр логов
./vendor/bin/sail logs
./vendor/bin/sail logs mysql

# Выполнение Artisan команд
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan route:list

# Выполнение Composer команд
./vendor/bin/sail composer install
./vendor/bin/sail composer require package/name

# Выполнение NPM команд
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# Доступ к MySQL CLI
./vendor/bin/sail mysql

# Доступ к Redis CLI
./vendor/bin/sail redis-cli

# Выполнение PHP команд
./vendor/bin/sail php --version
./vendor/bin/sail php artisan tinker
```

### Тестирование

```bash
./vendor/bin/sail artisan test
```

### Очистка кэша

```bash
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
```

### Создание нового контроллера

```bash
./vendor/bin/sail artisan make:controller YourController
```

### Создание новой миграции

```bash
./vendor/bin/sail artisan make:migration create_your_table
```

### Дополнительные сервисы

Проект включает следующие сервисы:

- **MySQL 8.0** - База данных (порт 3306)
- **Redis** - Кэш и очереди (порт 6379)
- **Mailpit** - Тестирование email (порт 8025 для веб-интерфейса)

Данные MySQL хранятся на хосте в папке `docker/mysql/data/`.

### Остановка и очистка

```bash
# Остановка контейнеров
./vendor/bin/sail down

# Остановка с удалением volumes (удалит данные БД!)
./vendor/bin/sail down -v
```

## Лицензия

Приложение является тестовым. Развернул для собственного развития, пощупать новую версию лары в связке с sanctum, чтоб понимать возможности библиотеки и что у нее внутри.
