# Growth MVP — Telegram-интеграция для магазинов цветов

MVP по ТЗ Posiflora: подключение Telegram-бота, уведомления о заказах, лендинг с двумя магазинами, личный кабинет.

**Стек:** Symfony 6.4 (PHP 8.2), React + TypeScript, MySQL 8.0, Memcached, Docker.

## Сервисы

| Сервис | URL |
|--------|-----|
| API (nginx) | http://localhost:5000/api |
| Frontend | http://localhost:5001 |
| phpMyAdmin | http://localhost:5002 |

## 1. Запуск backend (Docker)

```bash
cp .env.example .env
docker compose up --build -d
```

При старте выполняются только миграции. Данные в БД сохраняются при пересборке контейнеров.

Первичные данные (один раз):

```bash
docker compose exec backend_php php bin/console app:seed
```

Проверка API:

```bash
curl -X POST http://localhost:5000/api/shops/1/orders \
  -H "Content-Type: application/json" \
  -d '{"number":"A-1005","total":2490,"customerName":"Анна","count":1,"product_id":"a1"}'
```

## 2. Запуск backend (без Docker)

Требования: PHP 8.2+, Composer, MySQL 8.0, Memcached, OpenSSL.

```bash
cd backend
composer install
cp .env .env.local  # настройте DATABASE_URL, MEMCACHED_DSN
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:growth_mvp_jwt_pass 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:growth_mvp_jwt_pass
php bin/console doctrine:migrations:migrate
php bin/console app:seed
symfony server:start --port=5000
```

## 3. Запуск frontend

**Docker:** http://localhost:5001 (переменная `VITE_API_URL=http://localhost:5000`).

**Локально:**

```bash
cd frontend
npm install
VITE_API_URL=http://localhost:5000 npm run dev
```

## 4. Тестовые данные (сиды)

```bash
docker compose exec backend_php php bin/console app:seed
```

Команда **не трогает** БД, если магазины уже есть. Полная перезагрузка данных:

```bash
docker compose exec backend_php php bin/console app:seed --force
```

| Email | Пароль | Магазин |
|-------|--------|---------|
| dram1008@yandex.ru | password | Акация |
| owner@shik-blask.ru | password | Шик блеск красота |

Создаются также 5 заказов на каждый магазин.

Удалить том MySQL (полный сброс БД): `docker compose down -v && docker compose up --build -d`, затем `app:seed`.

## 5. Прогон тестов

```bash
docker compose exec backend_php php bin/phpunit
```

Сценарии (п. 11 ТЗ):

1. **SENT** — заказ с включённой интеграцией → вызов TelegramClient, лог `SENT`
2. **Идемпотентность** — повторная отправка не дублирует лог и не вызывает client
3. **FAILED** — ошибка Telegram → лог `FAILED`, заказ в БД есть
4. **skipped** — интеграция выключена → `sendStatus: skipped`, без лога

## 6. Telegram: mock vs реальная отправка

По умолчанию `TELEGRAM_MOCK=true` — используется `MockTelegramClient` (интерфейс `App\Telegram\TelegramClient`).

**Реальная отправка:**

1. В `docker-compose.yml` или `.env`: `TELEGRAM_MOCK=false`
2. Перезапустить backend: `docker compose up -d backend_php backend_nginx`
3. Войти в кабинет → **Настройки Telegram** (`/shops/{shopId}/growth/telegram`)
4. Указать `botToken` от [@BotFather](https://t.me/BotFather) и `chatId`

**Как узнать chatId:** напишите [@userinfobot](https://t.me/userinfobot) — вернёт ваш id. Для группы — id группы (часто отрицательный).

API: `POST https://api.telegram.org/bot{token}/sendMessage`

## 7. Допущения и упрощения

- Префикс API `/api` (в ТЗ пути без префикса)
- Лендинг, JWT-авторизация, два магазина — сверх минимального ТЗ
- `bot_token` хранится зашифрованным (AES-256-CBC), ключ `TELEGRAM_TOKEN_ENCRYPTION_KEY` в `.env`
- API заказа: `number`, `total`, `customerName`, `count`, `product_id`; телефон — в `customerName`
- Memcached — кэш Symfony
- JWT в `localStorage`

## Сценарий проверки

1. Открыть http://localhost:5001 — заказать букет
2. Войти как `dram1008@yandex.ru` / `password`
3. Настроить Telegram в `/shops/1/growth/telegram`
4. Создать ещё один заказ с лендинга — проверить уведомление и статус

## Структура

```
backend/   — Symfony API
frontend/  — React SPA
```
