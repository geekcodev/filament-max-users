---
tags: [ users, chats, edit, view, form, sections, ui, lang, tests, composer, dependencies, gate ]
date: 2026-11-23
---

# Секции страниц пользователя и чата

## Проблема

Формы и просмотры были плоскими: `EditMaxUser` без контекста записи, `ViewMaxUser` и `ViewMaxChat` — без карточек, тогда
как `EditMaxChat` уже использует `Section`. В просмотре пользователя повторяемая запись чатов выводила техническую
подпись `Max chats` вместо локализованного заголовка.

## Решение

`MaxUserForm`: две секции — «Служебные данные» (read-only `user_id`, аватар, `phone_verified_at`, `last_activity_time`,
`profile_checked_at`; `disabled()` + `dehydrated(false)`, даты в формате панели через
`getContainer()->getDefaultDateTimeDisplayFormat()`, активность — `UserPresenter::lastActivityAt()`, аватар — `Image`
только при заполненном `avatar_url`) и «Профиль и контакты» (e-mail после телефона, переключатели «Сбросить
подтверждение телефона» и «Является ботом» внизу одной строкой).

`ViewMaxUser`: секции «Служебные данные», «Профиль и контакты», «Чаты». `ViewMaxChat`: секции «Служебные данные» (ID,
тип, статус, иконка, активность, метаданные обновлены), «О чате» (название, ссылка, описание) и «Участники». У
`RepeatableEntry` по `maxChats` и `chatUsers` подпись скрыта через `hiddenLabel()`: без него заголовком становится имя
связи (`Max chats`, `Chat users`). Новые ключи `users.view.context|profile`, `chats.view.context|about` — в ru и en.

Constraints в `composer.json` переведены с `dev-new` на `^2.0` после выпуска ядра и адаптера `v2.0.0`.

Документация: README, AGENTS.md §5 и release notes v2.0.0 (пункты про секции, скрытые подписи, цифры Gate, таблица прав)
синхронизированы.

## Тесты

- Состав формы и infolist проверяется обходом `Section` → `getChildComponents()`.
- `test_edit_page_shows_read_only_service_data` — read-only поля контекста.
- `test_view_page_groups_entries_into_sections` — в обоих ресурсах.
- В тестах просмотра проверяется `isLabelHidden()` у `maxChats` / `chatUsers`.

## Нюансы

- `assertSee` по русской подписи не работает: локаль тестов — en.
- `Image` в форме не имеет `label()`, поэтому аватар выводится без подписи.
- `hiddenLabel()` обязателен: `RepeatableEntry` без явного label рендерит имя связи как заголовок.
- `getContainer()` возвращает `Schema` с `HasDefaultDataFormattingSettings`, формат даты совпадает с `->dateTime()`.
- Отдельные ключи для служебных полей не заводились: используются `users.view.*` / `chats.view.*`.

## Gate

Lint 0/33, PHPStan level max 0, PHPUnit 87/87 (439 assertions), покрытие 98.08% строк (817/833), `composer audit` 0.
