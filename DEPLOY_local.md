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

Для заполнения базы используйте сидеры. Сидеры выполняются в следующем порядке:

1. **UserSeeder** - создает первого пользователя (обычно админ)
2. **OptionSeeder** - создает опции и связывает их с админом
3. **CompanySeeder** - создает компании и связывает их с админо

#### Сидер пользователя

Данные админа можно настроить через переменные окружения в `.env` файле:

```env
ADMIN_USER_EMAIL=example@email.com
ADMIN_USER_NAME=Фонарев Константин Михайлович
ADMIN_USER_PHONE=+79083054488
ADMIN_USER_PASSWORD=6]cBmFeCV^8&~S]Z
```

Обычно секреты лежат в гитлабе или консуле. Если переменные не указаны, используются значения по умолчанию (указаны выше).

**Запуск сидера:**

```bash
sail artisan db:seed --class=UserSeeder
```

#### Сидер опций

Сидер `OptionSeeder` выполняет следующие действия:

1. **Создает опции** (если их еще нет):
   - `upload_if_wifi_online` - Выгружать осмотр только при Wi-Fi соединении
   - `save_photo_in_phone` - Дублировать все фотографии в галереи телефона
   - `touch_id_face_id_enabled` - Touch ID/Face ID и код пароль

2. **Связывает админа со всеми опциями**:
   - Находит админа по email из переменной `ADMIN_USER_EMAIL` (созданного UserSeeder)
   - Добавляет ему все опции
   - Устанавливает значение `value = true` для всех опций (все включены)

**Примечание:** Сидер можно запускать повторно. Он не создаст дубликаты опций и не удалит существующие связи.

**Запуск сидера опций:**

```bash
sail artisan db:seed --class=OptionSeeder
```

#### Сидер компаний

Сидер `CompanySeeder` выполняет следующие действия:

1. **Создает компании** (если их еще нет):
   - ООО Ромашка и чай (Москва)
   - ЗАО Сбербанан (Санкт-Петербург)
   - АО Вайбарис (Москва)
   - ООО Роялкомандор (Екатеринбург)
   - ИП Микуриоза и друзья по парте (Новосибирск)
   - ИП Вкусности для гурманов любящих здоровое питание (Казань)

2. **Связывает админа со всеми компаниями**:
   - Находит адмниа по email из `ADMIN_USER_EMAIL` (созданного UserSeeder)
   - Добавляет ему все компании
   - Устанавливает флаг `enabled` случайным образом (`true` или `false`) для каждой компании

**Примечание:** Сидер можно запускать повторно. Он не создаст дубликаты компаний и не удалит существующие связи.

**Запуск сидера компаний:**

```bash
sail artisan db:seed --class=CompanySeeder
```

#### Запуск всех сидеров

Для запуска всех сидеров в правильном порядке:

```bash
sail artisan db:seed
```

#### Пересборка базы данных с сидерами

Для полной пересборки базы данных (удаление всех таблиц и данных, применение миграций и запуск сидеров):

```bash
sail artisan migrate:fresh --seed
```

**Внимание:** Команда `migrate:fresh` удалит все данные в базе данных!

Для перезапуска только сидеров без удаления данных:

```bash
sail artisan db:seed
```
Можно наполнить тестовыми данные через консольную команду

```bash
sail artisan db:seed-test
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

Создать базу 
```bash
sail mysql -u root -proot
```
затем
```bash
CREATE DATABASE IF NOT EXISTS testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```
и запуск
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

