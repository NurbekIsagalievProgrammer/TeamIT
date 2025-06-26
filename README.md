# Task API (Symfony 7 + PostgreSQL)

## Описание
RESTful API для управления задачами (создание, просмотр, обновление, удаление, фильтрация, JWT-аутентификация).

---

## Требования
- PHP >= 8.2
- Composer
- PostgreSQL
- Node.js (только если нужны фронтенд-ассеты)

---

## Установка

1. **Клонируйте репозиторий:**
   ```bash
   git clone <your-repo-url>
   cd task-api
   ```

2. **Установите зависимости:**
   ```bash
   composer install
   ```

3. **Настройте переменные окружения:**
   - Скопируйте `.env` или создайте свой:
     ```env
     DATABASE_URL="postgresql://postgres:YOUR_PASSWORD@127.0.0.1:5432/TeamIT?serverVersion=16&charset=utf8"
     JWT_PASSPHRASE=your_jwt_passphrase
     JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
     JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
     ```

4. **Создайте ключи для JWT:**
   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```
   (Введи passphrase и пропиши его в `.env`)

5. **Создайте базу данных и выполните миграции:**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. **(Опционально) Загрузите тестовые данные:**
   ```bash
   php bin/console doctrine:fixtures:load
   ```

---

## Запуск

1. **Запустите сервер Symfony:**
   ```bash
   symfony server:start
   # или
   php -S 127.0.0.1:8000 -t public
   ```

2. **API будет доступен по адресу:**
   - http://127.0.0.1:8000/api/tasks

---

## Примеры запросов (Postman/cURL)

### Получить все задачи (GET)
```
curl -X GET http://127.0.0.1:8000/api/tasks -H "Authorization: Bearer <JWT>"
```

### Получить задачу по ID (GET)
```
curl -X GET http://127.0.0.1:8000/api/tasks/1 -H "Authorization: Bearer <JWT>"
```

### Создать задачу (POST)
```
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT>" \
  -d '{"name":"Test","description":"Desc","status":"новая"}'
```

### Обновить задачу (PUT)
```
curl -X PUT http://127.0.0.1:8000/api/tasks/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT>" \
  -d '{"name":"Updated","description":"New desc","status":"в процессе"}'
```

### Удалить задачу (DELETE)
```
curl -X DELETE http://127.0.0.1:8000/api/tasks/1 -H "Authorization: Bearer <JWT>"
```

### Получить JWT-токен (логин)
```
curl -X POST http://127.0.0.1:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","password":"your_password"}'
```

---

## Тесты

1. **Создайте тестовую базу и выполните миграции:**
   ```bash
   php bin/console doctrine:database:create --env=test
   php bin/console doctrine:migrations:migrate --env=test
   php bin/console doctrine:fixtures:load --env=test
   ```
2. **Запустите тесты:**
   ```bash
   php bin/phpunit
   ```

---

## Примечания
- Все ответы API — в формате JSON.
- Для доступа к защищённым эндпойнтам требуется JWT-токен.
- Для фильтрации и пагинации используйте query-параметры: `?status=в процессе&page=1&limit=10`

---

## Контакты
- Автор: nisagaliev035@gmail.com 