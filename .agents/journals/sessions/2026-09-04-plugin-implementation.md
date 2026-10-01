---
tags: [ v1.0.0, implementation, resources, tests ]
date: 2026-09-04
---

# Полная реализация плагина v1.0.0

## Проблема

Реализация плагина с нуля: два Filament-ресурса, инфраструктура качества и документация.

## Решение

- Каркас: `composer.json`, `phpstan.neon`, `phpunit.xml`, `.php-cs-fixer.dist.php`, `.gitignore`, `.env.example`,
  Docker.
- Конфиг `config/filament-max-users.php`, lang `ru`/`en`, плагин и сервис-провайдер.
- Ресурсы `MaxUserResource` и `MaxChatResource` с таблицами и страницами (список, просмотр).
- 12 feature-тестов: доступ, списки, просмотр, действия.
- `README.md`, release notes v1.0.0, CI workflow.

Ключевые решения: в Filament v5 для `infolist()` используется `Filament\Schemas\Schema`, а не
`Filament\Infolists\Infolist`; в тестах `Resource::getUrl('index')` вместо `route()` — совместимость с testbench;
счётчик чатов через `withCount('maxChats')`,
`maxUser` подгружается relationship-колонкой.

## Тесты

Шаги 0–17 плана закрыты, 12 feature-тестов покрывают права доступа, списки, страницы просмотра и действия.

## Нюансы

- Детали API чата отложены как опциональный feature и вернулись в 2026-10-01 вместе с `MaxChatProfileService`.
- Коммит выполняется пользователем, агент не коммитит без явного запроса.

## Gate

lint 0 · phpstan max 0 · phpunit 12/12 · audit 0.
