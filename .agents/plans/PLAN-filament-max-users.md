# PLAN — filament-max-users

> Автономный Filament-плагин **просмотра пользователей и чатов MAX-мессенджера** внутри Filament-панели: разделы
> «Max пользователи» и «Max чаты» в одной navigation-группе. Строится поверх `geekcodev/laravel-max-client`
> (реестр `max_users`/`max_chats`) и ядра `geekcodev/max-php-client` (Bot API MAX), по образцу
> [`geekcodev/filament-max-broadcasts`](https://github.com/geekcodev/filament-max-broadcasts) и
> [`geekcodev/filament-max-chat`](https://github.com/geekcodev/filament-max-chat).
>
> Этот файл — **рабочий план**. По мере реализации отмечай каждый пункт галочкой `[x]` (вместо `[ ]`), дописывай
> заметки и решения в секцию «Журнал сессий» внизу. Не удаляй выполненные разделы — помечай их.
>
> Рабочий язык — **русский**. Код-стайл и инфраструктура клонируются у плагина-примера `filament-max-broadcasts`.

---

## 0. Принятые решения (результат уточнений)

1. **Два раздела из одного плагина** — стандартный подход: плагин регистрирует **два** Filament-ресурса
   (`MaxUserResource` и `MaxChatResource`). Каждый ресурс = свой раздел.
2. **Одна navigation-группа** — оба ресурса получают одинаковую `getNavigationGroup()` (по умолчанию `'Max'`), поэтому в
   сайдбаре появляется один пункт-группа с двумя подпунктами:
    - «Max пользователи» (`MaxUserResource`);
    - «Max чаты» (`MaxChatResource`).
3. **Источник миграций и моделей** — НЕ создаём свои таблицы. Реестр уже есть в `laravel-max-client`:
    - `max_users` (`MaxUser`, PK `user_id`);
    - `max_chats` (`MaxChat`). Ресурсы плагина работают напрямую с этими моделями (`chats_model`/`users_model` из
      конфига ларvel-max-client). Миграции в плагине **не** нужны — они загружаются хостом из `laravel-max-client`.
4. **Раздел «Max пользователи»** (`MaxUserResource`):
    - список пользователей (имя, username, is_bot, активность, аватар);
    - детальный просмотр;
    - **количество чатов, в которых состоит пользователь** — счётчик через связь `MaxUser::maxChats()`;
    - **обновление данных пользователя из API MAX** — действие, вызывающее
      `GeekCo\LaravelMaxClient\Services\MaxUserProfileService::refresh($user_id)` (уже реализован в laravel-max-client,
      источник истины — `getChatMembers`).
5. **Раздел «Max чаты»** (`MaxChatResource`):
    - список чатов (user_id, chat_id, статус, последняя активность);
    - **детальный просмотр** (в т.ч. через `ApiClient::getChat($chatId)` при наличии API-доступа; иначе — только данные
      из реестра + связанный пользователь);
    - редактирования нет.
6. **Функции редактирования НЕ нужны.** Возможно только удаление — удалять можно только **запись чата** `MaxChat`
   из локального реестра (`max_chats`), а НЕ из MAX. Для пользователей удаление на текущем этапе не делаем (в реестре он
   источник данных API). Точное поведение удаления уточняется на этапе реализации.
7. **Зависимости** — из примера `filament-max-broadcasts`: `filament/filament ^5`, `geekcodev/laravel-max-client ^1.1`,
   `geekcodev/max-php-client ^1.0`, `laravel/framework ^13`, `livewire/livewire ^4`. Dev: php-cs-fixer, larastan,
   orchestra/testbench, phpstan-phpunit, phpunit. PHP ^8.4.
8. **Права по умолчанию** — конфигурируемые, по образцу примера: `users.view` / `users.manage` (просмотр и действия
   обновления/удаления) и `chats.view` / `chats.delete` — уточнить набор на этапе реализации.
9. **Blade-вьюхи / real-time** — на текущем этапе не используются (соответственно примеру).
10. **Эталон реализации** — `filament-max-broadcasts` (см. `/home/user/web/filament-max-broadcasts/`): структура src/,
    ServiceProvider, Plugin, конфиг, lang, тесты, Docker, Gate.
11. **Обновление профиля пользователя** — только через существующий `MaxUserProfileService` из laravel-max-client (не
    дублируем логику вызова `getChatMembers` в плагине). Прямые вызовы ApiClient из Filament — за
    `MaxUserProfileService`.

---

## 1. Цели и объём

**Что делает плагин.** Даёт в Filament-панели две navigation-группы в сайдбаре:

- **«Max пользователи»** — просмотр списка и деталей пользователей `max_users`, счётчик чатов, обновление профиля из MAX
  API;
- **«Max чаты»** — просмотр списка и деталей чатов `max_chats` (по возможности с данными из `ApiClient::getChat`).

**Что НЕ входит в текущий план:**

- редактирование пользователей/чатов (данные — источник API);
- создание/удаление пользователей и чатов в самом MAX (только просмотр);
- массовые операции;
- отправка сообщений из этого плагина (это `filament-max-broadcasts`);
- real-time / Blade-вьюхи.

---

## 2. Структура репозитория (целевая)

```
filament-max-users/
├── AGENTS.md                       # проектный контекст + правила (по образцу filament-max-broadcasts)
├── README.md                       # документация: установка, настройка, использование
├── LICENSE                         # MIT (уже есть)
├── composer.json
├── phpunit.xml
├── phpstan.neon
├── .php-cs-fixer.dist.php
├── .env.example
├── .gitignore
├── Dockerfile
├── docker-compose.yml
├── docker/config/usr/local/etc/php/conf.d/40-custom.ini
├── config/filament-max-users.php
├── lang/ru/users.php               # подписи ресурса «Max пользователи»
├── lang/ru/chats.php               # подписи ресурса «Max чаты»
├── lang/en/users.php
├── lang/en/chats.php
├── src/
│   ├── FilamentMaxUsersPlugin.php
│   ├── FilamentMaxUsersServiceProvider.php
│   ├── Enums/
│   │   └── (при необходимости)
│   ├── Models/                     # (переопределяемые обёртки — если понадобятся; по умолчанию используем
│   │   ...                          #  MaxUser/MaxChat из laravel-max-client)
│   ├── Services/
│   │   └── (при необходимости обёртки над MaxUserProfileService)
│   ├── Resources/
│   │   ├── MaxUserResource.php
│   │   │   └── Pages/ListMaxUsers.php
│   │   │   └── Pages/ViewMaxUser.php
│   │   ├── MaxChatResource.php
│   │   │   └── Pages/ListMaxChats.php
│   │   │   └── Pages/ViewMaxChat.php
│   └── Support/
│       ├── ChatPresenter.php
│       └── UserPresenter.php
├── tests/
│   ├── TestCase.php
│   ├── Fixtures/
│   │   ├── AdminPanelProvider.php
│   │   ├── TestUser.php
│   │   ├── MockHttpClient.php
│   │   └── Migrations/...
│   ├── Feature/Resources/
│   │   ├── MaxUserResourceTest.php
│   │   └── MaxChatResourceTest.php
│   └── Unit/Support/PresenterTest.php
├── scripts/check-coverage.php
├── .github/workflows/ci.yml
└── .agents/                       # рабочая память проекта (в git, но export-ignore для dist)
    ├── plans/PLAN-filament-max-users.md   # ЭТОТ файл-план
    ├── release/RELEASE_NOTES_v1.0.0.md   # release notes по образцу примера
    └── journals/
        ├── JOURNAL.md
        └── sessions/
```

> Пакет — композер-`library`, подключается в хост через `--path`-репозиторий или VCS; публичные ассеты — лишних нет.

---

## 3. Публичный API плагина

### 3.1 Plugin

```php
GeekCo\FilamentMaxUsers\FilamentMaxUsersPlugin
    ::make()
    ->userResource(MaxUserResource::class)   // опционально переопределение
    ->chatResource(MaxChatResource::class);  // опционально переопределение
```

- `getId()` → `'filament-max-users'`.
- `register(Panel $panel)`: `$panel->resources([$userResource, $chatResource])`.
- `boot(Panel $panel)`: пусто.

### 3.2 ServiceProvider

- `register()`: merge `config/filament-max-users.php`; биндинги (при необходимости).
- `boot()`: `loadTranslationsFrom` `lang/` → namespace `filament-max-users`; publishes config/lang (миграций НЕТ —
  реестр из laravel-max-client).

---

## 4. Конфигурация `config/filament-max-users.php`

Опции (все с `env()`-фолбэками, префикс `FILAMENT_MAX_USERS_*`; эталон — `.env.example`). Черновик:

```php
return [
    'permissions' => [
        'users.view'   => env('FILAMENT_MAX_USERS_PERMISSION_USERS_VIEW',   'users.view'),
        'users.manage' => env('FILAMENT_MAX_USERS_PERMISSION_USERS_MANAGE', 'users.manage'),
        'chats.view'   => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_VIEW',   'chats.view'),
        'chats.delete' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_DELETE', 'chats.delete'),
    ],

    // Модели (по умолчанию из laravel-max-client).
    'users_model' => GeekCo\LaravelMaxClient\Models\MaxUser::class,
    'chats_model' => GeekCo\LaravelMaxClient\Models\MaxChat::class,

    'ui' => [
        'navigation_group' => 'Max',
        // подписи/иконки/слаги для обоих ресурсов
    ],
];
```

---

## 5. Миграции и таблицы

Собственные миграции и таблицы в плагине **не создаются**. Реестр пользователей/чатов — из пакета
`laravel-max-client` (таблицы `max_users`, `max_chats`). Плагин лишь работает с этими моделями.

Замечание для тестов/hоста: перед использованием плагина хост должен опубликовать и выполнить миграции
`laravel-max-client`.

---

## 6. Раздел «Max пользователи» (`MaxUserResource`)

- Модель — `users_model` (`MaxUser`).
- Связь для счётчика чатов — `MaxUser::maxChats()` (HasMany по `user_id`).
- Страницы: `ListMaxUsers`, `ViewMaxUser`.
- Список (таблица): имя (first/last), `username`, `is_bot` (badge), `last_activity_time`, кол-во чатов
  (`withCount('maxChats')`), аватар (ImageColumn, при наличии).
- Просмотр: полный профиль (все поля), счётчик чатов, Action «Обновить из MAX» → `MaxUserProfileService::refresh()`.
- Действия: только просмотр + обновление профиля. Редактирование/создание — нет.

## 7. Раздел «Max чаты» (`MaxChatResource`)

- Модель — `chats_model` (`MaxChat`).
- Связь — `MaxChat::maxUser()` (BelongsTo по `user_id`).
- Страницы: `ListMaxChats`, `ViewMaxChat`.
- Список: `chat_id`, `user_id`, `status` (badge), `last_activity_at`, связанный пользователь (имя).
- Просмотр: данные реестра + детали чата из MAX (`ApiClient::getChat`) при доступности; опционально форма с инфо, без
  редактирования.
- Действия: удаление записи чата из локального реестра (`max_chats`) — по праву `chats.delete`. Редактирования нет.

---

## 8. Локальная инфраструктура (клонировать у `filament-max-broadcasts`)

- `composer.json` — namespace `GeekCo\\FilamentMaxUsers\\` → `src/`, autoload-dev → `tests/`, Laravel-провайдер в
  `extra.laravel.providers`, scripts `test/analyse/lint/format/security-audit`.
- `phpstan.neon` — `level: max` (Larastan), configDirectories, по образцу примера.
- `.php-cs-fixer.dist.php`, `phpunit.xml` (SQLite :memory:), `.gitignore`, `.env.example`.
- `Dockerfile` + `docker-compose.yml` + `docker/config/...` — скопировать у примера.

---

## 9. Тесты

- Feature: `MaxUserResourceTest` (доступ по праву, листинг, просмотр, действие «Обновить из MAX» с моком
  `MaxUserProfileService`), `MaxChatResourceTest` (доступ, листинг, просмотр, удаление чата).
- Unit: при наличии сервисных обёрток/резолверов — их тесты; связей `MaxUser::maxChats` / `MaxChat::maxUser`.
- Моки `MaxUserProfileService`/`ApiClient` — через `$this->mock()`.

---

## 10. Обязательный Gate перед завершением

1. `composer run lint` / `composer run format` (php-cs-fixer) → 0 ошибок.
2. `composer run analyse` (phpstan level max) → 0 ошибок.
3. `composer run test` (phpunit, failOnRisky/failOnWarning) → зелёные.
4. `composer run coverage` (phpunit + `scripts/check-coverage.php`) → ≥95% строк.
5. `composer run security-audit` (composer audit) → 0 критичных. (JS/Vite и npm-аудита в этом плагине нет — только PHP;
   CI — по образцу filament-max-broadcasts.)

---

## 11. Порядок реализации (пошагово, двигаться сверху вниз)

> Каждый шаг — маленький, после каждого запускается тест/статик-анализ. Отмечай `[x]`.

- [x] **0.** Создать каркас репозитория: `composer.json`, `phpstan.neon`, `.php-cs-fixer.dist.php`, `phpunit.xml`,
  `.gitignore`, `.env.example`, `LICENSE` (есть), `Dockerfile`, `docker-compose.yml`, `docker/config/...`.
- [x] **1.** `composer.json` — имя `geekcodev/filament-max-users`, namespace, зависимости, scripts, autoload.
- [x] **2.** Установить зависимости (`composer install` в контейнере, по образцу примера).
- [x] **3.** Посмотреть свежие модели/энумы `laravel-max-client` (MaxUser, MaxChat, MaxChatStatus,
  `MaxUserProfileService`) и зафиксировать контракты (§6/§7).
- [x] **4.** `config/filament-max-users.php` + `.env.example` синхронизация (§4).
- [x] **5.** `lang/{ru,en}/users.php` и `lang/{ru,en}/chats.php` (тексты ресурсов, лейблы из enum-ов).
- [x] **6.** `MaxUserResource` + `Pages/ListMaxUsers` + `Pages/ViewMaxUser` (§6).
- [x] **7.** Действие «Обновить из MAX» для пользователя через `MaxUserProfileService` (+ права/конфиг) (§6).
- [x] **8.** `MaxChatResource` + `Pages/ListMaxChats` + `Pages/ViewMaxChat` (§7).
- [x] **9.** Детальный просмотр чата (данные реестра + опционально `ApiClient::getChat`) (§7).
- [x] **10.** Действие удаления записи чата (`MaxChat`) по праву `chats.delete` (§7).
- [x] **11.** `FilamentMaxUsersPlugin` + `FilamentMaxUsersServiceProvider` — регистрация обоих ресурсов и
  navigation-группы (§3).
- [x] **12.** Тестовая инфраструктура: `tests/TestCase.php`, `Fixtures/AdminPanelProvider`, `Fixtures/TestUser`,
  миграции laravel-max-client в тестах (§9).
- [x] **13.** Feature-тесты `MaxUserResourceTest`, `MaxChatResourceTest` (§9).
- [x] **14.** `README.md` (по образцу примера, с учётом решений §0).
- [x] **15.** Полный Gate (§10) — зелёный целиком.
- [x] **16.** Оформить release notes v1.0.0 (по образцу примера; ныне `RELEASE_NOTES_v1.0.0.md`) — черновик, визируется
  пользователем перед коммитом.
- [x] **17.** CI workflow `.github/workflows/ci.yml` (по образцу filament-max-broadcasts).
- [ ] **18.** Коммит/публикация — **только по явному запросу пользователя** (правило AGENTS).

### 11.1 Переход на laravel-max-client 1.2 / max-php-client 1.1.8 (сессия 2026-10-01)

> Релиз `laravel-max-client` 1.2.0 сменил форму реестра чатов: одна строка `max_chats` на чат с ключом `chat_id`, связи
> с
> пользователями уехали в `max_chat_users`, а у `max_users` появились контакты и аватар. Шаги ниже обязательны — без них
> плагин работает со старой формой и не запускается.

- [x] **19.** Поднять зависимости: `laravel-max-client` ^1.2.0, `max-php-client` ^1.1.8, обновить vendor и транзитивные
  пакеты (`composer update -W`).
- [x] **20.** Перевести `MaxChatResource`/`MaxChatsTable`/`ViewMaxChat` на `chat_id`: метаданные (`title`,
  `description`,
  `link`, `icon_url`), тип чата, отметка `chat_checked_at`, счётчик и список участников из `chatUsers`.
- [x] **21.** Действие «Обновить из MAX» для чата через `MaxChatProfileService::sync($chatId)` (не `refresh()`), право
  `chats.manage` в конфиге и `.env.example`.
- [x] **22.** Показать контакты пользователя: `phone` (+ иконка по `phone_verified_at`), `email`, `name`, `description`,
  `profile_checked_at`, список чатов через `RepeatableEntry`, счётчик по `chatLinks`, фильтр «с телефоном».
- [x] **23.** Вынести логику подписей в `Support\ChatPresenter` и `Support\UserPresenter` (модель хоста переопределяется
  конфигом — атрибуты читаются через `getAttribute()`).
- [x] **24.** Переписать тесты под новую форму реестра; action-тесты — на настоящих final-сервисах с подменой PSR-18
  транспорта (`tests/Fixtures/MockHttpClient.php`, `$this->fakeMaxApi()`), добавить
  `tests/Unit/Support/PresenterTest.php`.
- [x] **25.** Обновить README (схема, `max:upgrade`, права, действия) и AGENTS.md (контракты §5, структура §4).
- [x] **26.** Gate: lint 0, phpstan max 0, phpunit 30/30, audit 0.
- [ ] **27.** Коммит/релиз — **только по явному запросу пользователя** (правило AGENTS). Release notes
  `RELEASE_NOTES_v1.1.0.md` не оформлялся: сначала нужно решение пользователя о версии и текстах релиза.

**После релиза (отдельная сессия, вне этого плана):**

- [ ] (позже) Подключение `filament-max-users` в хост-приложение и проверка navigation-группы в реальной панели.
- [x] Release notes под новую форму реестра — `RELEASE_NOTES_v1.1.0.md` (шаг 33); переход `max:upgrade` ломающий для
  хостов на 1.1.x, поэтому версия minor.

### 11.2 Workflow и качество процесса (сессия 2026-10-01, вторая)

> Цель — чтобы следующая сессия (в том числе после `git clone`) начиналась с актуальных правил и не наступала на уже
> известные грабли. Практики взяты из `AGENTS.md` пакета `laravel-max-client`.

- [x] **28.** Переработать `AGENTS.md`: статус и динамическая сверка версий, правило «регрессия в интеграционном
  приложении чинится здесь», различение «текст коммита» и «коммит», формат release-notes и `.agents/`, таблица
  соглашений с BC-совместимостью, разделы «Частые ошибки» и «Чек-лист перед завершением задачи».
- [x] **29.** Гейт покрытия: `scripts/check-coverage.php` (порог 95% строк), скрипт `composer coverage`, в CI
  `coverage: xdebug` и шаг «Tests and coverage»; тестами закрыты fallback-ветки `ChatPresenter` для переопределённой
  модели (95.66% → 97.18% строк).
- [x] **30.** Привести `JOURNAL.md` и файлы сессий к формату 4.1 (frontmatter, ≤5 КБ; вид журнала уточнён в шаге 34),
  синхронизировать README (блок локальной разработки) и пройти Gate целиком: lint 0, phpstan max 0, phpunit 35/35,
  покрытие 97.22%, audit 0.
- [x] **31.** Перевести рабочую память в `.agents/`: планы в `.agents/plans/`, release-notes в `.agents/release/`,
  журнал и сессии в `.agents/journals/` (`JOURNAL.md` + `sessions/`); перенести туда `PLAN-filament-max-users.md` и
  release notes v1.0.0, обновить ссылки в документации, оставить каталог в git и исключить из архива пакета через
  `.gitattributes` (`export-ignore`).
- [x] **32.** Провести аудит изменений ветки на соответствие `AGENTS.md`. Найдено и исправлено: в ресурсах не было
  `canCreate()`/`canEdit()`/`canDelete()` → `false` (добавлены вместе с feature-тестами на read-only контракт); в §5 и
  gotcha 3 было написано, что `counts()` ждёт имя колонки, а Filament v5 ждёт имя связи — текст правил приведён к коду
  (`counts('chatUsers')`, `counts('chatLinks')`); сломанное выравнивание блока `scripts` в `composer.json`. Уточнена
  BC-строка:
  тексты подписей править можно, фиксируя в release-notes (в `lang/en` исправлен русский текст в `resource.label` и
  `plural_label`). Проверено и в порядке: паритет ключей `lang/ru` ↔ `lang/en`, отсутствие прямых вызовов `ApiClient`,
  fail-closed права, baseline только из `tests/`, синхронность `config` ↔ `.env.example` ↔ README, отсутствие секретов,
  Gate: lint 0, phpstan max 0, phpunit 35/35 (123), покрытие 97.22%, audit 0.
- [x] **33.** Подготовить release notes: релиз ломающий (зависимость `laravel-max-client ^1.2.0`, новая форма реестра,
  `max:upgrade`, новое право `chats.manage`), поэтому версия minor — `.agents/release/RELEASE_NOTES_v1.1.0.md`; значимые
  пункты продублированы в README, раздел «История изменений».

- [x] **34.** Зафиксировать правила текста коммита (subject на английском по Conventional Commits, собирается по всему
  diff ветки, ломающий переход — `feat!:` или minor-релиз) и привести форматы рабочей памяти к образцу соседнего пакета:
  `journals/sessions/YYYY-MM-DD-<слаг>.md` с латинским slug и секциями `Проблема` / `Решение` / `Тесты` / `Нюансы` /
  `Gate`,
  `JOURNAL.md` как таблица `Дата` / `Файл` / `Теги` / `Описание`, release notes единого вида `RELEASE_NOTES_vX.Y.Z.md` —
  всё отражено в §4.1 `AGENTS.md` и в чек-листе.

- [x] **35.** Снять расхождение Gate с CI: `phpstan.neon` получил `reportUnmatchedIgnoredErrors: false` (без
  `composer.lock` CI резолвит более новые Filament/Livewire/Larastan — на локальном Filament 5.7.8 хелперные ошибки
  `TestResponse` есть и baseline совпадает, на CI-наборе Filament 5.9.0 / Livewire 4.4.7 / Larastan 3.12.2 / PHPStan
  2.2.16 их нет и все 15 записей baseline считаются незакрытыми). Проверка в условиях CI: скопировать дерево без
  `vendor/` и
  `composer.lock` в `.ci-sim/`, выполнить там `composer install` и Gate — каталог после проверки удалить. Отражено в
  gotcha 13 и 14.

---

## 12. Журнал сессий

Итоги сессий живут в `.agents/journals/JOURNAL.md` (таблица `Дата` / `Файл` / `Теги` / `Описание`) и в файлах
`.agents/journals/sessions/`. Здесь только ссылка: по правилу «что куда писать» из `AGENTS.md` факт о сессии не
дублируется в плане.

---

## 13. Открытые вопросы / заметки

- [x] Набор прав доступа: уточнить точные имена по образцу хоста (users.view / users.manage / chats.view /
  chats.delete). По умолчанию выставить конфигурируемый набор — значения уточняются при подключении в хост.
- [x] Детальный просмотр чата: живые данные `getChat` показаны как **действие** по праву `chats.manage`
  (`MaxChatProfileService::sync()`), а не как «живая вкладка» при открытии страницы. Так администратор сам решает, когда
  тратить запрос к API, и получает явный отчёт об успехе/неудаче вместо молчаливого деградирования (2026-10-01).
- [x] Удаление «возможно только»: уточнить, что именно удаляем (только запись чата в локальном реестре, без удаления в
  MAX; для пользователей удаление не предусмотрено).
- [x] Проверить: `withCount` на `MaxUser` и eager-load связей в таблицах, чтобы не росло число запросов.
- [x] Проверить сортировку по колонке-счётчику (`sortTable('chat_users_count')`) — работает, потому что Filament
  применяет
  `withCount` до билда запроса сортировки (2026-10-01).
- [ ] Проверить в реальной панели хоста: рендер аватаров/иконок по внешним URL (max.ru), поведение `max:upgrade` на
  данных хоста и перенос старых прав `chats.*` (новая `chats.manage` в конфиге по умолчанию — хост должен выдать её
  админам).
