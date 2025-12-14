# Развертывание и разработка

## Требования

- Docker и Docker Compose
- Git

> **Примечание:** Проект использует Laravel Sail для Docker-окружения. Все зависимости (PHP, MySQL, Redis) запускаются в контейнерах.

## Установка

### 1. Клонирование и установка зависимостей

Через временный контейнер. Запустит из текущей папки композер и наполнит vendor. Потом удалит контейнер.
```bash
docker run --rm -v ${PWD}:/app -w /app composer:latest install
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

### 7. Доступ к приложению

После запуска Sail приложение будет доступно по адресу:

```
http://localhost
```
> **Примечание:** По умолчанию Sail использует порт 80. Если нужно изменить порт, отредактируйте `APP_PORT` в `.env` файле.


### 6. Заполнение базы данных (сидеры)

Для заполнения базы данных тестовыми данными используются сидеры.

#### Сидер компаний

Сидер `CompanySeeder` выполняет следующие действия:

1. **Создает компании** (если их еще нет):
    - ООО Ромашка и чай (Москва)
    - ЗАО Сбербанан (Санкт-Петербург)
    - итп

2. **Связывает пользователей с компаниями**:
    - Обрабатывает только первых 10
    - Для каждого случайным образом выбирает от 0 до всех компаний
    - Устанавливает флаг `enabled` случайным образом (`true` или `false`)

**Примечание:** Сидер можно запускать повторно.

**Запуск сидера компаний:**

```bash
sail artisan db:seed --class=CompanySeeder
```

**Запуск всех сидеров:**

```bash
sail artisan db:seed
```

## Xdebug

Добавьте в .env файл:
```
XDEBUG_MODE=develop,debug
XDEBUG_CONFIG=client_host=host.docker.internal start_with_request=yes
```
затем перезапустите
```bash
sail restart
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

