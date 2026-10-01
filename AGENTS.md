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
      которых состоит пользователь, контакты (телефон/email/аватары), действие «Обновить из MAX»;
    - `MaxChatResource` («Max чаты») — список и детальный просмотр чатов `max_chats` (данные реестра + связанный
      пользователь), метаданные чата из MAX API, участники, действие «Обновить из MAX» и удаление локальной записи чата.
      Функции редактирования/создания **отсутствуют**: данные — источник Bot API MAX, плагин только показывает.
- **Принцип.** Плагин самодостаточен для просмотра, но **не создаёт собственных таблиц** — источник истины по
  пользователям/чатам — `laravel-max-client` (`max_users`, `max_chats`, модели `MaxUser`, `MaxChat`). От хост-приложения
  он ожидает: опубликованные миграции laravel-max-client, выполненный `php artisan max:upgrade`, настроенный API-доступ
  (токен) для действий «Обновить из MAX» и прав. Механизмы laravel-max-client не дублируются: профиль — только через
  `MaxUserProfileService`, метаданные чата — только через `MaxChatProfileService`.
- **Лицензия.** MIT (файл `LICENSE`).
- **Язык.** Рабочий язык общения с пользователем — **русский**; подписи UI — через lang-файлы (`lang/ru`, `lang/en`).

### Статус и версии

Актуальную версию **всегда сверяй по источникам, а не по цифрам в этом файле**: constraint и зафиксированная версия — в
`composer.json`, реально установленная — `composer show geekcodev/laravel-max-client` (и `geekcodev/max-php-client`),
релизные теги — `git tag --sort=-v:refname` в этом репозитории и в соседнем `../laravel-max-client`.

Текущий минимум: `laravel-max-client ^1.2.0` и `max-php-client ^1.1.8`. `^1.2.0` обязателен не по привычке: начиная с
этой версии реестр перешёл на **новую форму** — одна строка на чат, PK `chat_id`, связи вынесены в `max_chat_users`. На
`1.1.x` модели `MaxChat`/`MaxChatUser` не имеют `title`, `chat_type`, `chat_checked_at`, `status`, поэтому ресурсы
разваливаются (AttributeError на отсутствующих колонках) — понижать constraint нельзя.

### Регрессии не чиним в стороннем проекте

Если расхождение или пробел обнаружен в интеграционном Laravel-приложении, использующем этот пакет, — это регрессия
плагина, а не особенность приложения. Заводи задачу здесь (тест + фикс + релиз) и не предлагай обход в стороннем
репозитории, если обход маскирует дефект самого пакета.

## 2. Ветки, git и релизы

- `dev` — рабочая ветка разработки; `main` — стабильная, соответствует релизам; релиз — тег `vX.Y.Z`.
- `version` в `composer.json` **не указывается** — версия берётся из git-тегов.
- `.env`, `vendor/`, `composer.lock`, `.phpunit.cache/`, `.phpstan-cache/`, `build/`, `coverage/` — untracked (в
  `.gitignore`). **Никогда не коммитить секреты** (`MAX_API_TOKEN`, `MAX_WEBHOOK_SECRET`).
- Коммиты и push делает пользователь. **Не коммить и не пушить без явного запроса.**
- **Различай «текст коммита» и «коммит».** «Напиши текст коммита» — верни только subject (+ body по стилю репозитория),
  коммит не создавай. «Закоммить» — создай коммит. Не смешивай эти запросы.
- **Текст коммита — на английском, по Conventional Commits, по всем изменениям ветки.** Subject (head) составляется из
  реального diff ветки (`git status --short`, `git diff`, `git log`), а не из последнего действия: `feat:`, `fix:`,
  `docs:`,
  `chore:`, `refactor:`, `test:` — с маленькой буквы, без точки в конце, до 72 символов. Body (если он нужен) — по той
  же конвенции, с переносами на 100 символов, с описанием «что изменилось и почему», без пересказа кода. Если изменения
  содержат ломающий переход или миграцию данных — это либо `feat!:`/тело с `BREAKING CHANGE:`, либо minor-релиз.
- Перед коммитом обязательно `git status --short` и `git diff`: в индекс добавляй **явный список файлов**, а не
  `git add .`/`git add -A`. Перед релизом рабочее дерево должно быть чистым, кроме ожидаемых файлов релиза.
- **Релиз**: release-notes в `.agents/release/RELEASE_NOTES_vX.Y.Z.md` → merge PR `dev → main` →
  `git tag vX.Y.Z && git push origin vX.Y.Z` → GitHub Release из тега → Packagist (обновляется по webhook). Значимые
  пункты релиза продублировать в `README.md` (раздел «История изменений»).
- **Формат release-notes**: `Новое` / `Изменение (BC)` / `Затронутые сценарии` / `Качество` (тесты, покрытие, аудит).
  Файл не удаляется после релиза — история остаётся в `.agents/release/`.

## 3. Правила для ИИ-агентов

1. В начале работы прочитай `AGENTS.md`, `README.md` и `.agents/plans/PLAN-filament-max-users.md` целиком. Эталон
   реализации — плагин `filament-max-broadcasts` в соседнем каталоге `/home/user/web/filament-max-broadcasts/`, пример
   документированного workflow — `laravel-max-client` в `/home/user/web/laravel-max-client/`.
2. **Не коммить и не пушить без явного запроса пользователя.**
3. Перед завершением любой задачи, менявшей код, прогони обязательный Gate (раздел 7) целиком и сверься с чек-листом
   (раздел 11). Результаты не подменяй; недоступный шаг честно указывай в отчёте, а не пропускай молча.
4. Плагин реализуется как **самодостаточный пакет**, но **без дублирования laravel-max-client**: модели пользователей и
   чатов используются из `laravel-max-client` напрямую, собственных таблиц/миграций нет. Источник истины по MAX API —
   `max-openapi` и пакетные классы (раздел 9).
5. Не выдумывай сигнатуры MAX API и не дублируй логику клиента: профиль пользователя — только через
   `MaxUserProfileService::refresh()`, метаданные чата — только через `MaxChatProfileService::sync()`. Прямые вызовы
   `ApiClient` из Filament-ресурсов и страниц запрещены — оборачивай в сервисы/presenters.
6. Если для задачи чего-то не хватает (токен, сеть, контейнер, драйвер покрытия) — скажи об этом, а не упрощай задачу
   молча.
7. Ответы — краткие и по делу; в коде — без лишних комментариев и без декоративных символов (никаких «ёлочек», эмодзи,
   псевдографики в сообщениях, именах и документации): только русский и английский языки.
8. Текст в Markdown-файлах (AGENTS.md, README.md, release-notes и др.) пиши как человек: связный текст, абзацы, а не
   сплошные списки из буллетов. Списки — только когда действительно перечисляешь однородные пункты (Gate, gotchas,
   чек-лист).
9. `.env.example` — единственный эталон имён переменных плагина; при добавлении новой `FILAMENT_MAX_USERS_*` переменной
   синхронизируй его и `config/filament-max-users.php`.
10. Любой ключ перевода, который ты добавил в код, обязан существовать и в `lang/ru`, и в `lang/en` — иначе UI покажет
    сырые ключи, а тесты этого не поймают.
11. По завершении каждой сессии заноси итог по формату из 4.1: строка в `.agents/JOURNAL.md` + файл в
    `.agents/journals/sessions/`, отмечай выполненные пункты в `.agents/plans/PLAN-filament-max-users.md`. Каталог
    `.agents/`
    **коммитится** — прогресс должен быть виден после `git clone`.
12. Перед правкой проверь чужие ловушки из раздела 10, а не открывай их заново.

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
  Support/ChatPresenter.php            название чата: title / имя собеседника / chat_id
  Support/UserPresenter.php            иконка телефона по phone_verified_at
tests/                              PHPUnit + Orchestra Testbench
  Fixtures/                            AdminPanelProvider, TestUser, миграции laravel-max-client + users
  Fixtures/MockHttpClient.php          подмена PSR-18 транспорта MAX API (сервисы final — мокать нельзя)
  Unit/Support/PresenterTest.php
  Feature/Resources/                   MaxUserResourceTest, MaxChatResourceTest
scripts/check-coverage.php           проверка порога покрытия (≥95% строк) по build/coverage.xml
.github/workflows/ci.yml              lint → phpstan → phpunit + coverage gate → audit
Dockerfile                            PHP 8.4 (по образцу filament-max-broadcasts)
docker-compose.yml                    сервис app, user 1000:1000, volume ./
docker/config/usr/local/etc/php/conf.d/40-custom.ini
composer.json                         PSR-4 GeekCo\FilamentMaxUsers\, PHP ^8.4
phpunit.xml                           failOnRisky/failOnWarning; SQLite in-memory; source → src/
phpstan.neon                          level max (Larastan), configDirectories → config/
phpstan-baseline.neon                 только test-only записи для Filament/Livewire-assertion хелперов
.php-cs-fixer.dist.php                PSR-12 + declare_strict_types + no_unused_imports (finder: src, tests, config, scripts)
.env.example                          эталон имён переменных (FILAMENT_MAX_USERS_*)
.gitattributes                        export-ignore для .agents/, tests/, .github/ и dev-конфигов: чистый dist
.agents/                             рабочая память проекта: plans/, release/, journals/{JOURNAL.md, sessions/} (см. 4.1)
```

`resources/views/`, Livewire-компонентов, real-time (Echo/Reverb) и собственных миграций на текущем этапе **нет** —
просмотр целиком на Filament-компонентах поверх реестра laravel-max-client.

### 4.1. Прогресс, планы и release-notes

`.agents/` — единственное место рабочей памяти проекта: журнал, планы и release-notes лежат только здесь. Каталог
**коммитится** (осознанное отличие от `laravel-max-client`, где `.agents/` в `.gitignore`): смысл в том, чтобы следующая
сессия — в том числе на другой машине после `git clone` — продолжила с актуального места. Чтобы рабочая память не
попадала в публичный архив пакета, каталог перечислен в `.gitattributes` с `export-ignore` (то же для `tests/`,
`.github/` и файлов статики), а секретов в `.agents/` не пишется по правилам ниже. Если репозиторий когда-нибудь станет
приватным без зеркала — тогда `.agents/` можно убрать в `.gitignore`.

```
.agents/
  plans/                           многошаговые планы (PLAN-*.md)
  release/                         release-notes версий
  journals/
    JOURNAL.md                     таблица сессий, новые сверху
    sessions/                      подробности сессий
```

- `plans/PLAN-*.md` — многошаговые задачи, которые переживают одну сессию. Активный план проекта:
  `.agents/plans/PLAN-filament-max-users.md`; шаги отмечаются в нём, дублировать в другие файлы не надо.
- `release/RELEASE_NOTES_vX.Y.Z.md` — release-notes версий, по одной на версию, в своём формате; файлы не удаляются,
  история остаётся в git.
- `journals/JOURNAL.md` — таблица сессий, новые сверху, колонки `Дата`, `Файл`, `Теги`, `Описание`; в описании — что
  сделано и результат Gate. Над таблицей — заголовок и пояснение формата. Подробности — в файле сессии, здесь только
  указатель.
- `journals/sessions/YYYY-MM-DD-<слаг>.md` — тело сессии ≤5 КБ: YAML-frontmatter (`tags`, `date`), заголовок `# <тема>` без
  даты (дата — в имени файла и во frontmatter), затем секции `Проблема`, `Решение`, `Тесты`, `Нюансы`, `Gate`. Слаг
  латиницей в kebab-case: `2026-09-30-chat-meta-and-phone-capture.md`. Только факты и решения; пересказ кода и длинные
  логи не пишем.

### Что куда писать

| Вопрос                                   | Файл                                                         |
|------------------------------------------|--------------------------------------------------------------|
| «Как устроен проект и что нельзя делать» | `AGENTS.md` — контракты, соглашения, Gate, gotchas, чек-лист |
| «Что делаем сейчас и в каком порядке»    | `.agents/plans/PLAN-*.md` — шаги, критерии готовности        |
| «Что произошло в конкретной сессии»      | `.agents/journals/sessions/*.md` + строка в `JOURNAL.md`     |
| «Что вошло в релиз X.Y.Z»                | `.agents/release/RELEASE_NOTES_vX.Y.Z.md`                    |

Правило: постоянное решение — в `AGENTS.md`; решение по конкретной задаче — в плане; факт о сессии — в журнале. Текст
правил в плане и журнале не дублируем, даём ссылку на раздел `AGENTS.md`.

Чего в `.agents/` **не** пишем: токены и секреты, payload и тела ответов MAX API, содержимое чужих репозиториев,
устаревшие рассуждения. Не выдумывай результаты проверок: недоступный шаг Gate пишется как недоступный.

## 5. Архитектура и ключевые контракты

- **Подключение**: `->plugin(FilamentMaxUsersPlugin::make())` в PanelProvider. Регистрирует оба ресурса
  (`MaxUserResource`, `MaxChatResource`) в **одну navigation-группу** `Max` (`ui.navigation_group`).
- **Модели**: `users_model` → `GeekCo\LaravelMaxClient\Models\MaxUser` (таблица `max_users`, PK `user_id`, связи
  `chatLinks()` → `MaxChatUser` и `maxChats()` → `MaxChat`), `chats_model` → `GeekCo\LaravelMaxClient\Models\MaxChat`
  (таблица `max_chats`, **PK `chat_id`**, связь `chatUsers()` → `MaxChatUser`). Конфигурируемы через
  `config('filament-max-users.users_model')` / `chats_model`.
- **Форма реестра** (laravel-max-client ≥ 1.2, одна строка на чат): `max_chats.chat_id` + метаданные, `max_chat_users` —
  связь чата с пользователями (внешних ключей между таблицами нет). Плагин не создаёт схему сам.
- **Обновление после обновления пакета**: два шага, порядок обязателен — `php artisan migrate`, затем
  `php artisan max:upgrade` (есть `--dry-run` и `--rollback`). Пропуск второго шага оставляет старую форму реестра, и
  ресурсы покажут пустые или битые данные.
- **Права**: строки, `$user->can(...)` (совместимо со spatie/laravel-permission и Gate), конфигурируемы:
  `users.view` (доступ к разделу пользователей), `users.manage` (действие «Обновить из MAX» для пользователя),
  `chats.view` (доступ к разделу чатов), `chats.manage` (действие «Обновить из MAX» для чата), `chats.delete` (удаление
  записи чата из локального реестра).
- **Обновление профиля пользователя**: действие «Обновить из MAX» вызывает
  `GeekCo\LaravelMaxClient\Services\MaxUserProfileService::refresh($user_id)` (источник — `getChatMembers`).
- **Обновление метаданных чата**: действие «Обновить из MAX» вызывает
  `GeekCo\LaravelMaxClient\Services\MaxChatProfileService::sync($chat_id)` (источник — `getChat`), а не `refresh()`:
  администратор открыл конкретный чат, а `refresh()` пропускает чаты со свежим `chat_checked_at`.
- **Счётчики связей**: Filament `counts()` в v5 принимает **имя связи**, а не имя колонки: `counts('chatUsers')` у чата
  и
  `counts('chatLinks')` у пользователя. Laravel считает по snake_case-атрибуту `withCount`, поэтому переименование связи
  в
  `laravel-max-client` (`chatUsers()`, `chatLinks()`) сразу ломает счётчик.
- **Подписи состояния записи**: `Support\ChatPresenter` (название чата: `title` у группы и канала, имя собеседника у
  диалога, иначе `chat_id`) и `Support\UserPresenter` (иконка телефона по `phone_verified_at`). Атрибуты читаются через
  `getAttribute()`, потому что модель хоста переопределяется конфигом; оба presenter покрыты unit-тестами на обоих
  вариантах (реальная `MaxChat` и посторонняя модель).
- **Удаление**: запись `MaxChat` из локального реестра (`max_chats`) по праву `chats.delete`; перед удалением явно
  удаляются строки `chatUsers()` — иначе связи остались бы висеть без чата. Удаление пользователей и удаление объектов в
  самом MAX — **не** выполняем.
- **Редактирование** отсутствует: `canCreate()`/`canEdit()` → `false` (в Filament это не поведение по умолчанию, см.
  10).

### Соглашения

| Принцип            | Применение в этом пакете                                                                                                                                                                                                                                                                                                                  |
|--------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| PHP 8.4 / strict   | `declare(strict_types=1)` во всех файлах, PSR-12, PHPStan **level max** (Larastan), namespace `GeekCo\FilamentMaxUsers`                                                                                                                                                                                                                   |
| SOLID / DRY / KISS | Тонкие Filament-страницы; логика подписей и вычислений — в `Support\...`; без дублирования laravel-max-client                                                                                                                                                                                                                             |
| TDD                | Новый код покрыт тестами: unit — presenters/логика, feature — ресурсы, действия и права через Testbench                                                                                                                                                                                                                                   |
| Тестовые границы   | Сервисы `laravel-max-client` объявлены `final` — мокать нельзя: `$this->fakeMaxApi(...)` кладёт в контейнер PSR-18 клиент (`tests/Fixtures/MockHttpClient.php`) и проверяется настоящий путь через `ApiClient`                                                                                                                            |
| BC-совместимость   | В patch-релизе не меняются публичные сигнатуры, ключи config, имена lang-строк и значения по умолчанию config; ломающие изменения — только в minor/major с записью в release-notes. Тексты подписей (значения lang-строк) править можно, в том числе исправлять дефект вроде русского текста в `lang/en`, но фиксируй это в release-notes |
| Production-grade   | Fail-closed права, безопасные дефолты конфига, никаких секретов в коде/логах, ошибки API показываются пользователю и логируются                                                                                                                                                                                                           |
| Локализация        | Имена — английские; русские тексты — в `lang/ru` и тестах; enum-подписи через `->label()`, без хардкода в представлениях                                                                                                                                                                                                                  |

## 6. Локальная разработка

PHP/Composer на хосте не требуются — всё через Docker (по образцу `filament-max-broadcasts`):

```bash
docker compose up -d --build
docker compose run --rm app composer install
docker compose exec -T app composer lint      # php-cs-fixer --dry-run
docker compose exec -T app composer format    # php-cs-fixer fix
docker compose exec -T app composer analyse   # PHPStan level max
docker compose exec -T app composer test      # PHPUnit
docker compose exec -T app composer coverage  # PHPUnit + coverage gate ≥95%
docker compose exec -T app composer security-audit
```

Флаг `-T` у `docker compose exec` обязателен для неинтерактивных команд: без него вывод PHPUnit/PHPStan ломается при
перенаправлении и портит разбор результатов. Для разовой оболочки — `docker compose run --rm app bash`.

## 7. Обязательный Gate перед завершением задачи

После изменений в `src/`, `tests/`, `config/`, `scripts/`, `.github/`:

1. **Lint PHP**: `composer lint` (php-cs-fixer --dry-run) → 0 файлов с правками.
2. Если есть правки — `composer format`, затем повторить lint.
3. **Статика**: `composer analyse` (PHPStan level max) → 0 ошибок.
4. **Тесты**: `composer test` (PHPUnit) → зелёные (failOnRisky/failOnWarning).
5. **Покрытие**: `composer coverage` → ≥95% строк (`scripts/check-coverage.php`).
6. **Audit**: `composer security-audit` → 0 уязвимостей.

Все шаги обязательны. Недоступный шаг — честно в отчёт. Отчёт по Gate пишется в файл сессии (4.1): команды и их
результаты, без приписывания того, чего не запускалось.

## 8. OWASP Top 10 (обязательно при написании кода)

- **A01** — доступ к разделам и действиям только по правам (`permissions.*`); fail-closed, без прав — недоступно.
  `canAccess()`/`canView()` проверяют право просмотра; действия («Обновить из MAX», «Удалить») — право manage/delete.
- **A02** — секреты только в env; не логировать токены (`MAX_API_TOKEN`).
- **A03** — ввод с API (имена, username, описания) выводится через экранирующие компоненты Filament (по умолчанию);
  никакой raw-HTML из внешних данных.
- **A04** — удаление записи чата — серверная авторизация по праву, подтверждение на клиенте (`requiresConfirmation`); не
  доверяем `record` из запроса без проверки прав.
- **A05** — publishable-конфиг с безопасными дефолтами; секреты не в коде.
- **A06** — `composer security-audit` в Gate; зависимости из lock-файла актуальны.
- **A07** — права строкой через `$user->can(...)` (spatie/Gate); аутентификация/авторизация — за Filament.
- **A09** — ошибки API при «Обновить из MAX»/`getChat` логируются без чувствительных данных и не глушатся молча;
  пользователю показывается понятное уведомление об успехе/неудаче.

## 9. Источник истины (MAX API)

- Спецификация: `https://github.com/geekcodev/max-openapi` (OpenAPI 3.1), сервер `https://platform-api2.max.ru`.
- Сводка эндпоинтов, DTO и enums: `max-php-client/docs/api-reference.md` в соседнем репозитории — не дублируй её здесь,
  читай.
- Детали профиля пользователя: `ApiClient::getChatMembers` (через `MaxUserProfileService` из laravel-max-client).
- Детали чата: `ApiClient::getChat($chatId)` (через `MaxChatProfileService::sync`).
- Реестр чатов/пользователей — из `geekcodev/laravel-max-client` (`MaxChat`, `MaxUser`, `MaxChatStatus`).
- Сигнатуры брать из пакетных классов, не выдумывать. Факты, влияющие на адаптер: аутентификация — заголовок
  `Authorization: <token>` без `Bearer`; все timestamp API — Unix в **миллисекундах**; `chat_id`/`user_id` — int64 и
  могут быть **отрицательными** (группы и каналы); `message_id`/`callback_id` — строки; пагинация — `marker` + `count`.

## 10. Частые ошибки (gotchas)

1. **Filament v5, поиск**: `->searchable()` больше не принимает `columns:` — передаётся массив:
   `->searchable(['title'])`.
2. **Filament v5, ссылки**: у `TextEntry` внешняя ссылка — `->url(...)->openUrlInNewTab()`; `->openInNewTab()` не
   существует и даёт `BadMethodCallException`.
3. **`counts()` в Filament v5 ждёт имя связи**: `counts('chatUsers')`, `counts('chatLinks')`. Передача имени колонки
   (`chat_users_count`) или пустой вызов ничего не считает; переименование связи в `laravel-max-client` ломает счётчик.
4. **Filament v5, подписи**: `TextEntry::make('name')` в состоянии `false` не рендерится — тест «видно подпись» на
   `false`-значении не проходит и вводит в заблуждение; проверяй через значение атрибута, а не через наличие метки.
5. **Read-only плагин**: у Filament-ресурса создание и редактирование разрешены по умолчанию. Для этого пакета
   `canCreate()`/`canEdit()` обязаны возвращать `false`, иначе в UI появятся лишние кнопки.
6. **final-сервисы не мокаются**: `MaxUserProfileService`/`MaxChatProfileService` объявлены `final`, `$this->mock()` на
   них падает. Подменяй транспорт `ClientInterface` (`tests/Fixtures/MockHttpClient.php`).
7. **Порядок provider'ов в Testbench**: если Livewire регистрируется раньше Filament, хелперы `filament()::*` в тестах
   возвращают `void`/неинициализированное значение и тесты падают странно. Livewire подключай последним
   (`tests/TestCase.php`).
8. **Кэш PHPStan врёт**: устаревший `.phpstan-cache` даёт ложные краши Larastan
   (`Undefined constant Larastan\Larastan\LARAVEL_VERSION`) — лечится `rm -rf .phpstan-cache`.
9. **Packagist из контейнера**: если `composer install/update` зависает на сети, помогает `COMPOSER_IPRESOLVE=4`
   (`docker compose run --rm -e COMPOSER_IPRESOLVE=4 app composer update`); это временный флаг, в репозиторий его не
   коммитим.
10. **`docker compose exec` без `-T`** ломает пайпы и вывод PHPUnit/PHPStan — для неинтерактивных запусков добавляй
    `-T`.
11. **`php artisan max:upgrade` обязателен** после `migrate` при обновлении пакета — иначе реестр остаётся в старой
    форме, а ресурсы показывают пустые/битые данные.
12. **Идентификаторы MAX бывают отрицательными** (чаты групп и каналов) — не пиши тесты и фильтры в расчёте на
    положительные id, и не переноси это ограничение в свою схему.
13. **Baseline — только для тестовых хелперов**: в `phpstan-baseline.neon` допустимы записи про
    `assertCanSeeTableRecords`/`assertCanSeeText` и подобные; production-ошибки в baseline не добавлять — чинить код.
14. **Покрытие падает незаметно**: `composer test` не строит отчёт. После добавления кода гейт проверяет
    `composer coverage`; в контейнере нужен драйвер (`XDEBUG_MODE=coverage`), в CI — `coverage: xdebug`.

## 11. Чек-лист перед завершением задачи

- [ ] Gate пройден целиком: lint 0 файлов, PHPStan 0 ошибок, PHPUnit зелёные, покрытие ≥95%, audit чист.
- [ ] Новый код покрыт тестами (unit — presenters/вычисления, feature — ресурсы, действия, права, отказ при отсутствии
  прав).
- [ ] Публичный API не сломан: сигнатуры, ключи конфига, ключи переводов и дефолты совместимы с patch-релизом.
- [ ] Проверки прав fail-closed: без нужного `permissions.*` раздел недоступен, действие скрыто и отклоняется на
  сервере.
- [ ] Нет прямых вызовов `ApiClient` из ресурсов/страниц; обновление идёт через сервисы laravel-max-client.
- [ ] Каждый новый ключ перевода присутствует и в `lang/ru`, и в `lang/en`.
- [ ] Секретов нет в коде, логах, коммитах; `README.md`, `.env.example` и `AGENTS.md` синхронны с кодом.
- [ ] Обновлены `.agents/plans/PLAN-filament-max-users.md` и `.agents/journals/` (строка в `JOURNAL.md` + файл в
  `sessions/` по формату 4.1).
- [ ] Коммит/тег/push — только по явному запросу пользователя; иначе оставлено рабочее дерево и описан статус.
- [ ] Если просили текст коммита: subject на английском по Conventional Commits, собран по всему diff ветки, коммит не
  создан.