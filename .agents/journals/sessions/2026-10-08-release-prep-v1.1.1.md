---
tags: release, docs, security, gate
date: 2026-10-08
---

# Подготовка релиза v1.1.1: документация, аудит, безопасность ссылки чата

## Проблема

Код ветки v1.1.1 готов (порядок и состав колонок, infolistы, фикс участников, бамп filament), но перед релизом нужно
свести документацию с кодом и провести аудит против `AGENTS.md`, best practices и OWASP: чек-лист требует синхронности
`README` ↔ `.env.example` ↔ `config`, production-grade — безопасный ввод из MAX API (A03).

## Решение

- Найдено при аудите и исправлено: `.env.example` без `FILAMENT_MAX_USERS_NAVIGATION_GROUP`; блок config в README
  расходился с реальным `config/filament-max-users.php` (нет `env()` для `navigation_group`, `navigation_label`,
  `label`, `plural_label`); в README отсутствовала запись v1.1.1 в «История изменений»; `.gitattributes` без
  `phpunit.xml`, `Dockerfile`, `docker-compose.yml`, `docker/**`, `.php-cs-fixer.cache` (по образцу
  `filament-max-broadcasts`).
- A03 (XSS в href): `->url(fn => $state)` в колонке `link` списка чатов и в infolist страницы чата отдавал значение из
  MAX API в href без проверки схемы — `javascript:` срабатывал бы при клике. Добавлен `ChatPresenter::safeUrl()`:
  trim + разрешены только `http`/`https`, иначе `null` (ссылка не кликабельна). Применён в обоих местах.
- Release notes дополнены пунктом про `safeUrl`, исправлена формулировка про тесты (на каждый раздел три:
  порядок списка, toggleable-состав, порядок infolist), цифры Gate обновлены, добавлен итог аудита.
- В `.env.example` навигационная группа проставлена явно `=Max`: пустое значение подставлено бы вместо дефолта (`env()`
  не применяет дефолт к пустой строке), комментарий объясняет удаление строки.

## Тесты

12 DataProvider-кейсов `ChatPresenter::safeUrl` в `PresenterTest`: остаются `http`/`https` (включая регистр схемы и
пробелы по краям), обнуляются `javascript:` (в т.ч. с управляющими символами), `data:`, `mailto:`, ссылки без схемы,
битые URL, пустая строка и `null`. Итог по Gate: 54/54 (187 утверждений).

## Нюансы

- `parse_url` не находит схему после управляющих символов и на битых URL — такие значения попадают в ветку «схема не
  строка» и возвращают `null`; это fail-closed и соответствует замыслу (кроме http/https кликабельно ничего).
- Относительные ссылки (`max.ru/chat` без схемы) теперь тоже не кликабельны: `link` из MAX API — всегда абсолютный
  приглашение-URL, поэтому поведение на реальных данных не меняется.

## Gate

- `composer lint` — 0 файлов с правками из 22.
- `composer analyse` (PHPStan level max) — 0 ошибок.
- `composer test` — 54/54, 187 утверждений.
- `composer coverage` — 97.47% строк (501/514), порог 95%.
- `composer security-audit` — 0 уязвимостей (сеть до Packagist прошла напрямую, рецепт `pick_ip` не понадобился).
