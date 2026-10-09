---
tags: chats, diagnosis, getchat, participants, max-php-client, host-app
date: 2026-10-08
---

# «Не удалось обновить метаданные чата» — дефект ядра max-php-client

## Проблема

Запрос: ошибка на кнопке «Обновить из MAX» в разделе «Max чаты» — «Не удалось обновить метаданные чата. Проверьте токен
MAX и доступность API», при этом обновление пользователей работает. Путь: `ViewMaxChat::refreshChat`
→ `MaxChatProfileService::sync()` → `ApiClient::getChat()`, исключение перехватывается в `fetch()` (лог
`MAX getChat failed` пишется только при `MAX_LOGGING_ENABLED=true`) и превращается в `sync() === false` и уведомление.

## Решение

Причина не в токене и не в плагине. Воспроизведено на реальном API: `GET /chats/{id}` группы (HTTP 200) возвращает
`participants` объектом `{ "<user_id>": <время активности, мс> }`, а DTO ядра `Chat::fromArray()` разбирал поле как
список `User` и падал с `InvalidResponseException: Field "user_id" must be an integer`. Диалоги (`participants: null`) и
`getChatMembers` парсились нормально — отсюда видимость «пользователи обновляются, чаты нет». Фикс выполнен в ядре
`geekcodev/max-php-client`: `Json::intMap()`, `Chat::$participants` —
`array<int, int>` `user_id => активность`, тесты и записи в разделы 8 и 9 `docs/api-reference.md`; там же план, журнал и
`RELEASE_NOTES_v1.1.9.md`. Ядро выпущено: тег `v1.1.9` = `a701474` (PR #22), GitHub Release, Packagist — та же ссылка
(проверено по p2-метаданным Packagist). Vendor плагина обновлён до v1.1.9.

## Тесты

Новый регресс-тест плагина `test_refresh_metadata_action_parses_group_participants_map` (MaxChatResourceTest):
ответ `getChat` с картой `participants` → кнопка «Обновить из MAX» успешна, метаданные записаны; на ядре v1.1.8 тест
падал бы тем же исключением `InvalidResponseException`. Gate плагина: lint 0/22 · PHPStan 0 · PHPUnit 38/136 · покрытие
97.22% · audit 0. Gate ядра (v1.1.9): lint 0/107 · PHPStan 0 · PHPUnit 356/915 · покрытие 99.81% · audit 0; реальный
API — группа с картой из 10 участников, диалог — `participants: null`.

## Нюансы

- Vendor обновлён (`max-php-client` 1.1.8 → 1.1.9); ограничение `^1.1.8` уже допускало версию, правка constraint не
  понадобилась.
- Заодно `filament/filament` 5.7.8 → 5.9.0 (`--with-dependencies`, все filament-пакеты 5.9.0): в аудит прилетел advisory
  CVE-2026-104181 (medium, MFA без повторной проверки пароля, `<5.8.2`); constraint `^5.0`
  не менялся, audit снова 0.
- В хост-приложении возможен второй, независимый фактор отказа: образы без цепочки Минцифры (cURL 60) — см. сессию
  `2026-10-07-chat-refresh-permission-gap.md`; после обновления пакета проверить оба.
- Прямых чтений `Chat::$participants` в `laravel-max-client` и плагине нет.

## Gate

Плагин прогнан целиком после обновления vendor: `composer lint` — 0 из 22 файлов; `composer analyse` — 0 ошибок;
`composer test` — 38 тестов / 136 проверок; `composer coverage` — 97.22% строк (455/468, порог 95%);
`composer audit` — 0 уязвимостей (адаптер `pick_ip`: прямой запуск в контейнере падал по таймауту сети). Gate ядра (до
релиза): lint 0/107 · PHPStan 0 · PHPUnit 356/915 · покрытие 99.81% · audit 0.
