---
tags: ui, table, toggleable, users, chats, lang, tests
date: 2026-10-08
---

# Все колонки списков как toggleable

## Проблема

Пользователь хочет, чтобы на страницах со списком «Max пользователи» и «Max чаты» была доступна переключением каждая
возможная колонка реестра. Ранее перечисленные порядки закреплены по умолчанию видимыми, всё остальное — скрыто, но
включается тулбаром колонок.

## Решение

- `MaxUsersTable`: всем колонкам добавлен `->toggleable()`; по умолчанию видимы `user_id`, `is_bot`,
  `avatar_url`, `first_name`, `last_name`, `phone`, `chat_links_count` (порядок из задачи
  2026-10-08-users-table-column-order не изменился). Добавлены новые, скрытые по умолчанию, колонки:
  `full_avatar_url` (image), `name` (поиск/сортировка), `description` (limit 50), `phone_verified_at`
  (dateTime с placeholder «нет подтверждённого контакта»). Итоговый состав — 15 колонок.
- `MaxChatsTable`: все колонки сделаны `toggleable`; по умолчанию видимы `chat_id`, `chat_type`,
  `status`, `displayName`, `chat_users_count`, `last_activity_at` (порядок из задачи 2026-10-08-chats-table-column-order
  не изменился). Добавлены скрытые `description` (limit 50) и
  `link` (кликабельная ссылка при непустом значении) — их нет в таблице, хотя поля есть в `max_chats`. Итоговый состав —
  11 колонок.
- Lang: новые ключи `table.name`, `table.description`, `table.full_avatar_url`,
  `table.phone_verified_at` в `lang/{ru,en}/users.php` и `table.description`, `table.link` в
  `lang/{ru,en}/chats.php` — паритет ru/en соблюдён.

## Тесты

Два новых feature-теста `test_list_exposes_every_column_as_toggleable` (в `MaxUserResourceTest` и
`MaxChatResourceTest`): полный порядок `getColumns()` и `isToggleable()` для каждой колонки. Прежние тесты видимого
порядка (`..._expected_default_order`) не менялись и остались зелёными.

## Нюансы

- Служебные `created_at`/`updated_at` в колонки не выносились: «возможные» = поля данных реестра
  `max_users`/`max_chats` и вычисляемая `displayName`.
- Инфолисты страниц просмотра не менялись — задача только про списки.
- Порядок в массиве `columns()` сохраняет видимый порядок: скрытые колонки стоят рядом со своими полными «соседями»
  (аватары, имя/фамилия/описание, телефон/подтверждение), чтобы при включении тулбаром они появлялись в осмысленных
  позициях.

## Gate

- `composer lint` — 0 из 22 файлов с правками.
- `composer analyse` — 0 ошибок.
- `composer test` — 42/42 (175 assertions).
- `composer coverage` — 97.44% строк (494/507), порог 95%.
- `composer security-audit` — 0 уязвимостей (рецепт `pick_ip`).
