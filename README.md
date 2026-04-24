# Распознавание автора

Веб-приложение на PHP + Nginx с сервисом ИИ на Python (сиамская модель) для сравнения рукописного текста.

## Структура проекта

- `src/` - PHP-приложение (контроллеры, сервисы, представления, публичные точки входа)
- `ai/` - FastAPI-сервис ИИ
- `ai/model/` - файл модели (`handwriting_expert_epoch_5.pth`)
- `docker/postgres/init/` - SQL-схема для PostgreSQL

Изображения эталонов и проверок хранятся в PostgreSQL (BYTEA), а не в папках.

## Локальный запуск (Docker Compose)

1. Поместите файл модели:
   - `ai/model/handwriting_expert_epoch_5.pth`
2. Запуск:

```bash
docker compose up --build
```

- Сайт: `http://localhost:8080`
- Админка: `http://localhost:8080/admin.php`

Остановка:

```bash
docker compose down
```

Сброс базы:

```bash
docker compose down -v
```

## Деплой на Railway

Нужно 3 сервиса:

1. **Postgres** (Railway plugin)
2. **AI service** (из `ai/Dockerfile`)
3. **Web service** (из `Dockerfile.railway`)

### 1) Postgres

- Добавьте PostgreSQL plugin в проект Railway.
- Скопируйте переменные подключения в сервисы `web` и `ai`:
  - `DB_HOST`
  - `DB_PORT`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASSWORD`

### 2) AI service

- New Service -> Deploy from GitHub Repo
- Root Directory: `ai`
- Dockerfile Path: `ai/Dockerfile`
- Переменные:
  - DB-переменные (из Postgres)
  - `MODEL_PATH=/app/model/handwriting_expert_epoch_5.pth`
  - `AI_THRESHOLD=0.3`
- Файл модели добавьте как Volume/attached storage в `ai/model`.

### 3) Web service

- New Service -> Deploy from GitHub Repo
- Root Directory: `.`
- Dockerfile Path: `Dockerfile.railway`
- Переменные:
  - DB-переменные (из Postgres)
  - `AI_API_URL=http://<ai-service-internal-url>/predict`
    - внутренний URL берите из Railway Networking у AI сервиса.

После деплоя откройте домен web-сервиса.

## Тесты

Docker:

```bash
docker compose -f docker-compose.test.yaml up --build --abort-on-container-exit
```

Локально:

```bash
vendor/bin/phpunit -c phpunit.xml
pip install -r ai/requirements.txt
python -m pytest ai/tests -q
```