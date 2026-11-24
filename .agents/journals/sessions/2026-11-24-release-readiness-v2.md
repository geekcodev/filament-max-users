---
tags: [ release, audit, docs, gate, ci, composer ]
date: 2026-11-24
---

# Проверка готовности к релизу v2.0.0

## Проблема

Перед выпуском v2.0.0 нужно было сверить код, документацию и release notes с фактическим состоянием ветки и Gate.

## Решение

Проверены `composer.json` (constraints `^2.0`, `dev-new` не используется), README, AGENTS.md,
`.agents/release/RELEASE_NOTES_v2.0.0.md`, `.gitattributes`, CI, конфиг и lang-файлы. Найдено и исправлено:

- в release notes разделитель таблицы «Было → Стало» имел отступ 4 пробела против 2 у остальных строк — таблица не
  распознавалась как таблица внутри пункта списка; отступы выровнены;
- в AGENTS.md §5 для кнопки «Открыть пользователя» остались старые имена прав `users.edit` / `users.view` — заменены на
  `max_user.edit` / `max_user.view`;
- в README «История изменений» v2.0.0 страница просмотра была описана только для пользователя — добавлены секции чата и
  выровнен абзац про права.

Отдельно проверено: паритет ключей `lang/ru` ↔ `lang/en` (96/96 в users, 91/91 в chats), значения permission-строк в
бридже (`max_user.*` / `max_chat.*`), статусы чатов в `laravel-max-client::enums.chat_status`, отсутствие секретов и
отладочного кода, соответствие README блоку `config/filament-max-users.php`.

## Тесты

- Gate в рабочем контейнере: lint 0/33, PHPStan max 0, PHPUnit 87/87 (439), покрытие 98.08%, audit 0.
- Симуляция CI по gotcha 14: дерево без `vendor/` и `composer.lock` в `.ci-sim/`, `composer install` с нуля (Filament
  5.12.0, Larastan 3.15.0, оба geekcodev-пакета v2.0.0) и весь Gate — те же результаты. Каталог удалён.

## Нюансы

- Локальные теги были устаревшими (`v1.1.1` отсутствовал); после `git fetch --tags` подтверждено, что `origin/main`
  стоит на `v1.1.1` (`9432228`), а ветка `new` опережает `origin/dev` на 9 коммитов без обратных расхождений.
- Релиз идёт по процессу AGENTS: `new` → `dev` → PR `dev` → `main` → тег `v2.0.0`; тег пока не создавался.
- `composer.lock` в `.gitignore`, поэтому после смены constraints в индекс попадает только `composer.json`.

## Gate

Lint 0/33, PHPStan level max 0, PHPUnit 87/87 (439 assertions), покрытие 98.08% строк (817/833), `composer audit` 0.
Повторено на чистой установке зависимостей в `.ci-sim` — зелёное.
