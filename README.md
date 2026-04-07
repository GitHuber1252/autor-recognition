# Распознавание автора

Веб-приложение на PHP + Nginx с сервисом искусственного интеллекта на Python (сиамская модель) для определения сходства рукописного текста.

## Структура проекта

- `src/` - PHP-приложение (контроллеры, сервисы, представления, публичные точки входа)
- `ai/` - FastAPI-сервис ИИ
- `ai/model/` - контрольная точка модели (`.pth`)
- `ai/etalons/` - изображения рукописного ввода в etalons

## Первая настройка

1. Поместите файл модели в:

- `ai/model/handwriting_expert_epoch_5.pth`
2. Поместите изображения etalons в:

- `ai/etalons/`

## Запуск приложения

```bash
docker compose up --build
```

- Сайт: `http://localhost:8080`
- Страница администратора (etalons): `http://localhost:8080/admin.php`

## Остановка приложения

```bash
docker compose down
```

## Запуск тестов (Docker)

```bash
docker compose -f docker-compose.test.yaml up --build --abort-on-container-exit
```

## Запуск тестов (локально, необязательно)

PHP:

```bash
vendor/bin/phpunit -c phpunit.xml
```

Python:

```bash
pip install -r ai/requirements.txt
python -m pytest ai/tests -q
```