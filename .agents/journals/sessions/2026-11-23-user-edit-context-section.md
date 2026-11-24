---
tags: [ users, edit, view, form, sections, ui, lang, tests, composer, dependencies, gate ]
date: 2026-11-23
---

# Секции страниц пользователя

## Проблема

Страница `EditMaxUser` показывала только редактируемые поля без визуального контекста записи, а infolist `ViewMaxUser`
был плоским списком, тогда как `EditMaxChat` содержит `Section`-карточку с фоном для read-only данных. Нужно было
привести обе страницы пользователя к тому же виду.

## Решение

`Resources\Schemas\MaxUserForm` разбит на две секции. Верхняя «Служебные данные» (`users.form.context`) показывает
`user_id`, аватар, `phone_verified_at`, `last_activity_time` и `profile_checked_at`: поля `disabled()` и
`dehydrated(false)`, даты форматируются в формате панели Filament через
`$component->getContainer()->getDefaultDateTimeDisplayFormat()`, `last_activity_time` переводится в Carbon через
`UserPresenter::lastActivityAt()`. Аватар — `Schemas\Components\Image` (виден только при заполненном `avatar_url`, alt
из имени и фамилии). Нижняя секция «Профиль и контакты» (`users.form.profile`) содержит прежний набор редактируемых
полей в две колонки. Обе секции — `columnSpanFull()`. Ключи `users.form.context` / `users.form.profile` добавлены в
`lang/ru` и `lang/en`. В блоке «Профиль и контакты» e-mail перенесён сразу после телефона, переключатели «Сбросить
подтверждение телефона» и «Является ботом» стоят внизу одной строкой.

`ViewMaxUser::infolist()` разбит на три секции-карточки: «Служебные данные» (ID, тип, аватар, подтверждение телефона,
последняя активность, синхронизация), «Профиль и контакты» и «Чаты» (`RepeatableEntry` по `maxChats`). Подпись у
повторяемой записи убрана, чтобы не дублировать заголовок секции. Новые ключи `users.view.context` /
`users.view.profile`
добавлены в оба lang-файла.

После выпуска ядра и адаптера `v2.0.0` constraints в `composer.json` переведены с `dev-new` на `^2.0`; `composer update`
поставил обе зависимости v2.0.0.

Документация: в release notes v2.0.0 добавлены пункты про секции формы и просмотра, обновлены цифры «Качества» и убран
дублирующий пункт про диалог чата; README («Пользователи», «Изменить пользователя», «История изменений» v2.0.0) и
контракт §5 `AGENTS.md` синхронизированы с кодом.

## Тесты

- `test_edit_form_exposes_only_editable_fields` обновлён: обход верхнего уровня с раскрытием `Section` и ожидаемый
  список из служебных и редактируемых полей.
- Новый `test_edit_page_shows_read_only_service_data`: заголовки секций, аватар в HTML, `assertFormFieldDisabled` для
  `user_id`, `phone_verified_at`, `profile_checked_at` и `last_activity_time`.
- `test_view_page_shows_entries_in_expected_order` переведён на обход секций, добавлен
  `test_view_page_groups_entries_into_sections` с проверкой заголовков.

## Нюансы

- `assertSee` по русской подписи в тестах не работает: локаль тестов — en, поэтому заголовки сверяются через
  `__('filament-max-users::users.form.*')`.
- `Image` в схеме формы не имеет `label()`, поэтому аватар выводится без подписи; при пустом `avatar_url` скрыт.
- `getContainer()` у form-компонента возвращает `Schema`, который использует `HasDefaultDataFormattingSettings`, —
  формат даты совпадает с `->dateTime()` в таблицах и infolist.
- Отдельные ключи для служебных полей не заводились: используются существующие `users.view.*`.

## Gate

Lint 0/33, PHPStan level max 0, PHPUnit 86/86 (433 assertions), покрытие 98.05% строк (803/819), `composer audit` 0.
