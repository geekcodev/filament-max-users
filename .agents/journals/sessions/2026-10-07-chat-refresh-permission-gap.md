---
tags: ui, permissions, diagnosis, filament-max-users, chats, host-app
date: 2026-10-07
---

# Кнопка обновления чата: запрос на добавление оказался скрытым правом хоста

## Проблема

Запрос: «для пользователя есть обновление из MAX, для чатов нет — добавь кнопку на страницу просмотра чата». Кнопка в
коде есть: действие `refreshChat` в
`src/Resources/Pages/ViewMaxChat.php` (коммит `689bb50`, в релизе начиная с `v1.1.0`), с ним lang-ключи
`chats.actions.refresh*` и три feature-теста
`test_refresh_metadata_action_*`. В `v1.0.0` её нет — там только `delete`.

## Решение

Код пакета не менялся: расхождение оказалось во внешнем приложении i2tech.local. Там установлен `v1.1.0`, но право
`chats.manage` не объявлено в `UserPermissionEnum`, не выдано ролям и отсутствует в таблице `permissions`, поэтому
`->authorize()` fail-closed прячет кнопку; `users.manage` объявлено и попадает в админ-ролям через `cases()` — отсюда
наблюдение «у пользователя есть, у чата нет». Правка выполнена в i2tech.local: case
`ChatsManage` с лейблом, ключ `chats.manage` в опубликованном конфиге и env-строки,
`php artisan permissions:create`, feature-тест
`tests/Feature/Filament/MaxChatsResourceTest.php` на `assertActionVisible`/`assertActionHidden`.

## Тесты

По пакету: `composer test -- --filter refresh` — 6 passed (36 assertions); правок в пакете нет, полный набор не
перегонялся.

## Нюансы

Приложения на `v1.0.0` (chisto-service-mini-app, empty_bot) кнопки не получат до обновления до `^1.1.0` — вместе с
обязательным `laravel-max-client ^1.2` и двумя шагами
`migrate` → `max:upgrade`. В i2tech.local локально БД была пустой, `permissions:create`
прогнан; образы окружения собраны без CA Минцифры, поэтому фактический вызов `getChat`
из кнопки там упадёт до пересборки образов (см. план
`2026-10-07-max-chats-title-diagnosis.md` в i2tech).

## Gate

Код пакета не менялся — Gate пакета не запускался; частичная проверка — `composer test
--filter refresh` 6/6 зелёные. Gate i2tech.local пройден целиком: Pint 192 файла · PHPStan 0 · 303 теста · ESLint 0 ·
Prettier OK · оба audit 0.
