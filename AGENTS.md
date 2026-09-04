# AGENTS.md

> Проектный контекст и рабочие правила для разработчиков и ИИ-агентов (включая opencode).
> Читай этот файл **целиком** в начале работы — он задаёт архитектуру, обязательный процесс проверок (Gate)
> и требования SOLID / DRY / KISS / OWASP Top 10.
> Пользовательскую документацию (установка, быстрый старт, интеграция) — в `README.md`.

## 1. О проекте

- **Что это.** Filament-плагин **`geekcodev/filament-max-users`** — просмотр пользователей и чатов MAX-мессенджера
  внутри Filament-панели, в виде двух разделов («Max пользователи» и «Max чаты») в одной navigation-группе. Строится
  поверх `geekcodev/laravel-max-client` (реестр `max_users`/`max_chats`) и ядра `geekcodev/max-php-client` (Bot API
  MAX). Репозиторий/рабочая папка — `filament-max-users`, переиспользуемый автономный пакет.
- **Что даёт.** Два Filament-ресурса из одного плагина:
    - `MaxUserResource` («Max пользователи») — список и детальный просмотр пользователей `max_users`, счётчик чатов, в
      которых состоит пользователь, действие «Обновить из MAX» (данные профиля из Bot API);
    - `MaxChatResource` («Max чаты») — список и детальный просмотр чатов `max_chats` (данные реестра + связанный
      пользователь), опционально детали чата из MAX API; действие удаления локальной записи чата. Функции
      редактирования/создания **отсутствуют**: данные — источник Bot API MAX, плагин только показывает.
- **Принцип.** Плагин самодостаточен для просмотра, но **не создаёт собственных таблиц** — источник истины по
  пользователям/чатам — `laravel-max-client` (`max_users`, `max_chats`, модели `MaxUser`, `MaxChat`). От хост-приложения
  он ожидает: опубликованные миграции laravel-max-client, настроенный API-доступ (токен) для действий «Обновить из MAX»
  и прав. Механизмы laravel-max-client не дублируются: обновление профиля — только через
  `GeekCo\LaravelMaxClient\Services\MaxUserProfileService`.
- **Лицензия.** MIT (файл `LICENSE`).
- **Язык.** Рабочий язык общения с пользователем — **русский**; подписи UI — через lang-файлы (`lang/ru`, `lang/en`).

## 2. Ветки и состояние git

- `dev` — рабочая ветка разработки; `main` — стабильная, соответствует релизам; релиз — тег `vX.Y.Z`.
- `version` в `composer.json` **не указывается** — версия берётся из git-тегов.
- `.env`, `vendor/`, `composer.lock`, `.phpunit.cache/`, `build/`, `coverage/` — untracked (в `.gitignore`). **Никогда
  не коммитить секреты** (`MAX_API_TOKEN`, `MAX_WEBHOOK_SECRET`). Коммиты и push делает пользователь — без явного
  запроса не коммить.

## 3. Правила для ИИ-агентов

1. В начале работы прочитай `AGENTS.md`, `README.md` и `PLAN-filament-max-users.md` (эталон реализации — плагин
   `filament-max-broadcasts` в соседнем каталоге `/home/user/web/filament-max-broadcasts/`).
2. **Не коммить и не пушить без явного запроса пользователя.**
3. Перед завершением любой задачи, менявшей код, прогони обязательный Gate (раздел 7) целиком. Результаты не подменяй;
   недоступный шаг честно указывай в отчёте, а не пропускай молча.
4. Плагин реализуется как **самодостаточный пакет**, но **без дублирования laravel-max-client**: модели пользователей и
   чатов используются из `laravel-max-client` напрямую, собственных таблиц/миграций нет. Источник истины по MAX API —
   `max-openapi` и пакетные классы `laravel-max-client`/`max-php-client` (см. раздел 9).
5. Не выдумывай сигнатуры MAX API и не дублируй логику клиента: обновление профиля пользователя — только через
   `MaxUserProfileService::refresh()`; детали чата — через `ApiClient::getChat()`. Прямые вызовы ApiClient из
   Filament/Page запрещены — оборачивай в сервисы.
6. Если для задачи чего-то не хватает (токен, сеть, контейнер) — скажи об этом, а не упрощай задачу молча.
7. Ответы — краткие и по делу; в коде — без лишних комментариев.
8. Текст в Markdown-файлах (AGENTS.md, README.md, RELEASE и др.) пиши как человек: связный текст, абзацы, а не сплошные
   списки из буллетов. Списки — только когда действительно перечисляешь однородные пункты.
9. `.env.example` — единственный эталон имён переменных плагина; при добавлении новой `FILAMENT_MAX_USERS_*`
   переменной синхронизируй его и `config/filament-max-users.php`.
10. По завершении каждой сессии заноси краткий итог (что сделано, какие решения/отклонения, прогресс по задачам) в
    журнал прогресса `.ai/progress/` (единый `JOURNAL.md` + отдельный файл в `sessions/`) — чтобы следующая сессия
    продолжалась с актуального места и прогресс был виден после `git clone`. Отмечай выполненные пункты в
    `PLAN-filament-max-users.md`.

## 4. Структура репозитория (целевая)

```
config/filament-max-users.php       publishable-конфиг (--tag=filament-max-users-config)
lang/{ru,en}/users.php              подписи ресурса «Max пользователи»
lang/{ru,en}/chats.php              подписи ресурса «Max чаты»
src/
  FilamentMaxUsersServiceProvider.php  composition root: config/lang publish (миграций НЕТ — реестр из laravel-max-client)
  FilamentMaxUsersPlugin.php           Filament v5 plugin: оба ресурса в панели
  Enums/                               (при необходимости)
  Models/                              (обёртки — если понадобятся; по умолчанию — MaxUser/MaxChat из laravel-max-client)
  Services/                            (при необходимости обёртки над MaxUserProfileService / ApiClient)
  Resources/
    MaxUserResource.php                Filament-ресурс «Max пользователи»
    MaxChatResource.php                Filament-ресурс «Max чаты»
    (Pages/ListMaxUsers, ViewMaxUser, ListMaxChats, ViewMaxChat)
    (Tables/..., Schemas/... по образцу filament-max-broadcasts)
  Support/                             (при необходимости)
tests/                              PHPUnit + Orchestra Testbench
  Fixtures/                            AdminPanelProvider, TestUser, миграции laravel-max-client + users
  Unit/
  Feature/Resources/                   MaxUserResourceTest, MaxChatResourceTest
Dockerfile                            PHP 8.4 (по образцу filament-max-broadcasts)
docker-compose.yml                    сервис app, user 1000:1000, volume ./
docker/config/usr/local/etc/php/conf.d/40-custom.ini
composer.json                         PSR-4 GeekCo\FilamentMaxUsers\, PHP ^8.4
phpunit.xml                           failOnRisky/failOnWarning; SQLite in-memory
phpstan.neon                          level max (Larastan), configDirectories → config/
.php-cs-fixer.dist.php                PSR-12 + declare_strict_types + no_unused_imports
.env.example                          эталон имён переменных (FILAMENT_MAX_USERS_*)
```

`resources/views/`, Livewire-компонентов, real-time (Echo/Reverb) и собственных миграций на текущем этапе **нет** —
просмотр целиком на Filament-компонентах поверх реестра laravel-max-client.

## 5. Архитектура и ключевые контракты

- **Подключение**: `->plugin(FilamentMaxUsersPlugin::make())` в PanelProvider. Регистрирует оба ресурса
  (`MaxUserResource`, `MaxChatResource`) в **одну navigation-группу** `Max` (`ui.navigation_group`).
- **Модели**: `users_model` → `GeekCo\LaravelMaxClient\Models\MaxUser` (Таблица `max_users`, PK `user_id`, связь
  `maxChats()`), `chats_model` → `GeekCo\LaravelMaxClient\Models\MaxChat` (таблица `max_chats`, связь `maxUser()`).
  Конфигурируемы через `config('filament-max-users.users_model')` / `chats_model`.
- **Права**: строки, `$user->can(...)` (совместимо со spatie/laravel-permission и Gate), конфигурируемы:
  `users.view` (доступ к разделу пользователей), `users.manage` (действие «Обновить из MAX»), `chats.view` (доступ к
  разделу чатов), `chats.delete` (удаление записи чата из локального реестра).
- **Обновление профиля пользователя**: действие «Обновить из MAX» вызывает
  `GeekCo\LaravelMaxClient\Services\MaxUserProfileService::refresh($user_id)` (источник — `getChatMembers`). Плагин
  оборачивает вызов в свой сервис и не дублирует логику.
- **Счётчик чатов пользователя**: `withCount('maxChats')` на `MaxUser` (HasMany по `user_id`).
- **Детальный просмотр чата**: данные реестра `max_chats` + связанный пользователь; опционально живой профиль чата через
  `ApiClient::getChat($chatId)` — **грациозно** (без падения при отсутствии токена/сети; при недоступности — только
  данные реестра).
- **Удаление**: только запись `MaxChat` из локального реестра (`max_chats`) по праву `chats.delete`. Удаление
  пользователей и удаление объектов в самом MAX — **не** выполняем.
- **Редактирование** отсутствует: `canCreate()`/`canEdit()` → `false`.

### Соглашения

- PHP **8.4**, `declare(strict_types=1)` во всех файлах, PSR-12, PHPStan **level max** (Larastan).
- Namespace `GeekCo\FilamentMaxUsers` (тесты `GeekCo\FilamentMaxUsers\Tests`), PSR-4.
- SOLID / DRY / KISS: тонкие Filament-страницы, логика — в сервисах; без дублирования laravel-max-client.
- Не добавлять комментарии без необходимости. Имена — английские; русские тексты — в lang-файлах и тестах.
- Enum-ы (напр. `MaxChatStatus` из laravel-max-client): русские подписи через `->label()`, не хардкод в представлениях.
- Тесты обязательны для нового кода: unit — сервисы/enums/связи; feature — Filament-ресурсы через Testbench + фикстуры.
  Моки `MaxUserProfileService`/`ApiClient` — через `$this->mock()`.

## 6. Локальная разработка

PHP/Composer на хосте не требуются — всё через Docker (по образцу `filament-max-broadcasts`):

```bash
docker compose up -d --build
docker compose run --rm app composer install
docker compose exec app composer test      # PHPUnit
docker compose exec app composer analyse   # PHPStan level max
docker compose exec app composer lint      # php-cs-fixer --dry-run
docker compose exec app composer format    # php-cs-fixer fix
docker compose exec app composer audit     # composer audit
```

Если `composer analyse` падает с подозрительным крашем (например, `Undefined constant Larastan\Larastan\
LARAVEL_VERSION` в `LarastanStubFilesExtension`), сначала удали кэш PHPStan — устаревший `.phpstan-cache` вызывает
ложные краши:

```bash
docker compose exec app rm -rf .phpstan-cache
```

## 7. Обязательный Gate перед завершением задачи

1. **Lint PHP**: `composer lint` (php-cs-fixer --dry-run) → 0 ошибок; при правках — `composer format`.
2. **Статика**: `composer analyse` (PHPStan level max) → 0 ошибок.
3. **Тесты**: `composer test` (PHPUnit) → зелёные (failOnRisky/failOnWarning).
4. **Audit**: `composer audit` → 0 критичных.

Все шаги обязательны. Недоступный шаг — честно в отчёт.

## 8. OWASP Top 10 (обязательно при написании кода)

- **A01** — доступ к разделам и действиям только по правам (`permissions.*`); fail-closed, без прав — недоступно.
  `canAccess()`/`canView()` проверяют право просмотра; действия («Обновить из MAX», «Удалить») — право manage/delete.
- **A02** — секреты только в env; не логировать токены (`MAX_API_TOKEN`).
- **A03** — ввод с API (имена, username, описания) выводится через экранирующие компоненты Filament (по умолчанию);
  никакой raw-HTML из внешних данных.
- **A04** — удаление записи чата — серверная авторизация по праву, подтверждение на клиенте (`requiresConfirmation`); не
  доверяем `record` из запроса без проверки прав.
- **A05** — publishable-конфиг с безопасными дефолтами; секреты не в коде.
- **A06** — `composer audit` в Gate; lock-файлы актуальны.
- **A07** — права строкой через `$user->can(...)` (spatie/Gate); аутентификация/авторизация — за Filament.
- **A09** — ошибки API при «Обновить из MAX»/`getChat` логируются без чувствительных данных и не глушатся молча;
  пользователю показывается понятное уведомление об успехе/неудаче.

## 9. Источник истины (MAX API)

- Спецификация: `https://github.com/geekcodev/max-openapi` (OpenAPI 3.1), сервер `https://platform-api2.max.ru`.
- Детали профиля пользователя: `ApiClient::getChatMembers` (через `MaxUserProfileService` из laravel-max-client).
- Детали чата: `ApiClient::getChat($chatId)`. Сигнатуры брать из пакета `geekcodev/max-php-client`, не выдумывать.
- Реестр чатов/пользователей — из `geekcodev/laravel-max-client` (`MaxChat`, `MaxUser`, `MaxChatStatus`).
