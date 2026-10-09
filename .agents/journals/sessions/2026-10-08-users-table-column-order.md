---
tags: ui, table, infolist, users, lang, tests
date: 2026-10-08
---

# Порядок колонок и подписей в «Max пользователи»

## Проблема

Два запроса в одну сессию. Первый: порядок колонок в списке — Id, Тип (бот), Аватар, Имя, Фамилия, Телефон, Чатов;
фактический порядок в `MaxUsersTable` был другим, фамилия была скрыта по умолчанию. Второй: `is_bot` — признак бота,
поэтому подпись должна остаться «Бот»; для `username` в русском переводе — «Никнейм»; и детальная страница пользователя
должна показывать поля в порядке: id пользователя, бот, аватар, никнейм, имя, фамилия, отображаемое имя, описание,
телефон, телефон подтверждён, почта, последняя активность, профиль обновлён.

## Решение

`src/Resources/Tables/MaxUsersTable.php`: колонки переставлены в запрошенный порядок — `user_id`,
`is_bot` (badge), `avatar_url`, `first_name`, `last_name`, `phone`, `chat_links_count`. Фамилия стала видимой по
умолчанию (`toggleable()` без `isToggledHiddenByDefault`); `username`, `email`,
`last_activity_time`, `profile_checked_at` остаются в таблице, но скрыты по умолчанию
(`toggleable(isToggledHiddenByDefault: true)`) и включаются тулбаром колонок.

Подпись `table.is_bot` после уточнения возвращена к «Бот»/«Bot» (запрос «если is_bot — признак бота, оставь Бот»).
`table.username` и `view.username` в `lang/ru` переведены как «Никнейм»; английские значения оставлены «Username».

`src/Resources/Pages/ViewMaxUser.php`: infolist перестроен в запрошенный порядок — `user_id`,
`is_bot`, `avatar_url`, `username`, `first_name`, `last_name`, `name`, `description`, `phone`,
`phone_verified_at`, `email`, `last_activity_time`, `profile_checked_at`; повторяемый блок чатов (`maxChats`) остался
последним. Состав полей не менялся, только порядок.

## Тесты

Два feature-теста фиксируют порядок как контракт: `test_list_shows_columns_in_expected_default_order`
(`getVisibleColumns()` по `array_keys`) и `test_view_page_shows_entries_in_expected_order`
(`getSchema('infolist')->getComponents()`, имена через `getName()` у `Entry`). Существующий
`assertTableColumnStateSet('email', ...)` от скрытия не зависит — он проверяет состояние, а не видимость.

## Нюансы

Цепочка `Livewire::test(...)->assertSuccessful()->instance()` типизируется PHPStan как
`TestResponse` (assert* объявлены в `@mixin`), поэтому тесты разбивают цепочку и сужают тип через
`assertInstanceOf`. У базового `Filament\Schemas\Components\Component` метода `getName()` нет — он появляется у
`Filament\Infolists\Components\Entry` (трейт `HasName`), поэтому проверка идёт через
`instanceof Entry`. Публичный API не тронут: ключи lang и конфига те же, изменены только значения подписей и
порядок/видимость колонок — учитывается в release-notes ближайшего релиза.

## Gate

- `composer lint` — 0 из 22 файлов с правками;
- `composer analyse` (level max) — 0 ошибок;
- `composer test` — 37 tests, 130 assertions, зелёные;
- `composer coverage` — 97.22% строк (порог 95%) — пройден;
- `composer security-audit` — недоступен: контейнер не выходит на `repo.packagist.org`/`packagist.org`
  (`curl error 28`, connection timed out), повтор и с `COMPOSER_IPRESOLVE=4` не помог; зависимости в этой сессии не
  менялись.
