# [2026-09-02] Инициализация проекта и базовой документации

## Сделано

- Создан `AGENTS.md` — проектный контекст, правила для ИИ-агентов, обязательный Gate, OWASP Top 10, локальная
  разработка (по образцу `/home/user/web/filament-max-broadcasts/AGENTS.md`).
- Создан рабочий план `PLAN-filament-max-users.md` с разделами: принятые решения (§0), цели и объём (§1), структура
  репозитория (§2), публичный API (§3), конфигурация (§4), миграции/таблицы (§5), описание двух разделов (§6–7),
  инфраструктура (§8), тесты (§9), Gate (§10), порядок реализации (§11), журнал сессий (§12), открытые вопросы (§13).
- Заведён журнал прогресса `.ai/progress/` (`JOURNAL.md` + первый файл в `sessions/`).

## Изученные источники (эталон реализации)

- `filament-max-broadcasts` — структура src/, Plugin, ServiceProvider, конфиг, lang, тесты, Gate, Docker, журнал.
- `laravel-max-client` — модели `MaxUser` (PK `user_id`, связь `maxChats()`), `MaxChat` (связь `maxUser()`), enum
  `MaxChatStatus`, сервис `MaxUserProfileService` (`refresh()`/`ensureAvatar()`/`upsertFromMember()`), миграции
  `max_users`/`max_chats`.
- `max-php-client` — `ApiClient` (в т.ч. `getChat(int $chatId)` и `getChatMembers`).

## Решения

- Один плагин регистрирует два ресурса (`MaxUserResource`, `MaxChatResource`) в одну navigation-группу `Max`.
- Собственные миграции/таблицы не создаём — реестр потребитель берёт из `laravel-max-client`.
- Обновление профиля пользователя — только через `MaxUserProfileService::refresh()`, без дублирования логики.
- Детальный просмотр чата — данные реестра + связанный пользователь; `ApiClient::getChat` — опционально и грациозно.
- Редактирование отсутствует; удаление — только локальной записи чата `MaxChat`.

## Прогресс по чек-листу (§11 PLAN)

0 из 18 шагов — реализация ещё не начата (созданы только AGENTS.md, PLAN и журнал).
