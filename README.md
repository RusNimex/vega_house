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

Через временный контейне. Запустит из текущей папки композер и наполнит vendor. Потом удалит контейнер.
```bash
docker run --rm -v ${PWD}:/laravel-test -w /laravel-test composer:latest install
```

### 2. Настройка окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Настройки для Sail уже предустановлены в `.env.example`. При необходимости отредактируйте файл `.env`.

### 3. Запуск Laravel Sail

Alias для удобства (добавьте в `~/.bashrc` или `~/.zshrc`):

```bash
alias sail='./vendor/bin/sail'
```
После этого можно использовать просто `sail up -d`. Либо через полный путь `./vendor/bin/sail <command>`

Запустите Docker-окружение:

```bash
sail up -d
```

### 4. Генерация ключа приложения

```bash
sail artisan key:generate
```

### 5. Выполнение миграций

```bash
sail artisan migrate
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

### Swagger документация

API документирован с помощью Swagger/OpenAPI. После установки зависимостей и генерации документации, Swagger UI будет доступен по адресу:

```
http://localhost/api/doc
```

Для генерации документации выполните:

```bash
sail artisan l5-swagger:generate
```

> **Примечание:** Документация генерируется автоматически при установке пакета. Для обновления документации после изменения аннотаций в контроллерах, выполните команду `l5-swagger:generate`.

## Xdebug

Добавьте в .env файл:
```
XDEBUG_MODE=develop,debug
XDEBUG_CONFIG=client_host=host.docker.internal start_with_request=yes
```
затем перезапустим
```bash
sail restart
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
│   ├── Exceptions/
│   │   └── Handler.php                # Обработчик исключений
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php         # Базовый контроллер
│   │   │   ├── V1/
│   │   │   │   └── AuthController.php # Контроллер аутентификации v1
│   │   │   └── V2/
│   │   │       └── AuthController.php # Контроллер аутентификации v2
│   │   ├── Kernel.php                 # HTTP Kernel
│   │   └── Middleware/                # Middleware
│   ├── Models/
│   │   └── User.php                   # Модель пользователя
│   └── Providers/
│       ├── AppServiceProvider.php     # App Service Provider
│       └── AuthServiceProvider.php    # Auth Service Provider
├── bootstrap/
│   ├── app.php                        # Конфигурация приложения
│   └── cache/                         # Кэш bootstrap
├── config/
│   ├── app.php                        # Конфигурация приложения
│   ├── auth.php                       # Конфигурация аутентификации
│   ├── cors.php                       # CORS настройки
│   ├── database.php                   # Конфигурация БД
│   └── sanctum.php                    # Конфигурация Sanctum
├── database/
│   ├── factories/                     # Фабрики для тестов
│   ├── migrations/                    # Миграции БД
│   └── seeders/                       # Сидеры БД
├── routes/
│   ├── v1/
│   │   └── api.php                    # API маршруты v1
│   ├── v2/
│   │   └── api.php                    # API маршруты v2
│   ├── console.php                    # Консольные команды
│   └── web.php                        # Web маршруты
├── storage/                           # Хранилище файлов и логов
├── tests/                             # Тесты
├── docker-compose.yml                 # Laravel Sail конфигурация
├── docker/
│   ├── mysql/
│   │   └── data/                      # Данные MySQL (на хосте)
│   └── php/                           # PHP конфигурация
└── .env                               # Переменные окружения
```

## Разработка с Laravel Sail

### Основные команды Sail

```bash
# Запуск контейнеров
sail up -d

# Остановка контейнеров
sail stop
sail start
sail restart

# Просмотр логов
sail logs
sail logs mysql

# Выполнение Artisan команд
sail artisan migrate
sail artisan key:generate
sail artisan route:list

# Выполнение Composer команд
sail composer install
sail composer require package/name

# Выполнение NPM команд
sail npm install
sail npm run dev

# Доступ к MySQL CLI
sail mysql

# Доступ к Redis CLI
sail redis-cli

# Выполнение PHP команд
sail php --version
sail php artisan tinker
```

### Тестирование

```bash
sail artisan test
```

### Очистка кэша

```bash
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
```

### Создание нового контроллера

```bash
sail artisan make:controller YourController
```

### Создание новой миграции

```bash
sail artisan make:migration create_your_table
```

### Дополнительные сервисы

Проект включает следующие сервисы:

- **MySQL 8.0** - База данных (порт 3306)
- **Redis** - Кэш и очереди (порт 6379)
- **Mailpit** - Тестирование email (порт 8025 для веб-интерфейса)

Данные MySQL хранятся на хосте в папке `docker/mysql/data/`. Дайте докеру полные права на эту папку.

### Остановка и очистка

```bash
# Остановка контейнеров
sail down

# Остановка с удалением volumes (удалит данные БД!)
sail down -v
```

## Лицензия

Приложение является тестовым. Развернул для собственного развития, пощупать новую версию лары в связке с sanctum, чтоб понимать возможности библиотеки и что у нее внутри.
