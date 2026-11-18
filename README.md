# filament-max-users

Filament-плагин для **просмотра пользователей и чатов** MAX-мессенджера внутри Filament-панели.

Два раздела в одной navigation-группе «Max»:

- **Max пользователи** — список и профили пользователей `max_users`: телефон, e-mail, аватар, счётчик и список чатов,
  обновление профиля из MAX API, редактирование профиля и контактов в локальном реестре, удаление записи из реестра.
- **Max чаты** — список и детали чатов `max_chats`: название, описание, ссылка, иконка из MAX API, участники чата,
  обновление метаданных, редактирование метаданных и удаление записи из реестра.

> Данные — источник Bot API MAX. Создание пользователей и чатов отсутствует; локальные записи можно редактировать и
> удалять.

## Зависимости

- PHP ^8.4
- Laravel ^13
- Filament ^5
- `geekcodev/laravel-max-client` ^2.0 (реестр `max_users` / `max_chats` / `max_chat_users`; предрелизная ветка `new`
  ставит `dev-new` из Packagist)
- `geekcodev/max-php-client` ^2.0 (там же)

## Установка

```bash
composer require geekcodev/filament-max-users
```

Опубликуйте миграции `laravel-max-client`:

```bash
php artisan vendor:publish --tag=laravel-max-client-migrations
php artisan migrate
```

## Обновление с laravel-max-client < 1.2

В `laravel-max-client` 1.2.0 реестр чатов стал «одна строка на чат» (`max_chats.chat_id` вместо `user_id` + `id`), а
связи чата с пользователями переехали в `max_chat_users`. Плагин работает только с новой формой, поэтому после
`php artisan migrate` обязателен апгрейд схемы:

```bash
php artisan max:upgrade --dry-run   # посмотреть, что изменится
php artisan max:upgrade             # перенести данные в форму v1.2.0
```

Откат — `php artisan max:upgrade --rollback`. До `migrate` и `max:upgrade` страницы «Max чаты» работать не будут: модель
`MaxChat` ждёт новую форму, а в старой колонки `chat_id` нет.

## Подключение к панели

В `AdminPanelProvider` (или аналогичном PanelProvider):

```php
use GeekCo\FilamentMaxUsers\FilamentMaxUsersPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentMaxUsersPlugin::make());
}
```

## Конфигурация

```bash
php artisan vendor:publish --tag=filament-max-users-config
```

Файл `config/filament-max-users.php` содержит только UI-настройки; права, Policy-классы и модели берутся из
`laravel-max-client`. Плагин не использует env-переменные:

```php
return [
    'ui' => [
        'navigation_group' => 'Max',
        'users' => [
            'navigation_icon'  => 'heroicon-o-users',
            'navigation_sort'  => 1,
            'navigation_label' => null,
            'label'            => null,
            'plural_label'     => null,
            'slug'             => 'max-users',
        ],
        'chats' => [
            'navigation_icon'  => 'heroicon-o-chat-bubble-left-right',
            'navigation_sort'  => 2,
            'navigation_label' => null,
            'label'            => null,
            'plural_label'     => null,
            'slug'             => 'max-chats',
        ],
    ],
];
```

Модели и права настраиваются в `config/laravel-max-client.php`: `users.model`, `chats.model` и `permissions.*`.

Подписи ресурсов (`label`, `plural_label`, `navigation_label`) по умолчанию `null`: значения берутся из `lang/ru` и
`lang/en` по текущей локали. Задавайте их в конфиге только как осознанный override для конкретного приложения — иначе
переводы не будут работать.

## Права доступа

Права, Policy-классы и их регистрация живут в `laravel-max-client` (`MaxUserPolicy` / `MaxChatPolicy`). Плагин только
привязывает ability к страницам и действиям Filament: `viewAny` → доступ к разделу, `view` → карточка, `edit` →
редактирование, `delete` → удаление, `manage` → «Обновить из MAX». Код ресурса — префикс permission-строки (`max_user` /
`max_chat`); строки проверяются через Gate, поэтому работают spatie/laravel-permission и обычные
`Gate::define`.

| Право пользователя  | Назначение                              |
|---------------------|-----------------------------------------|
| `max_user.view.any` | Доступ к разделу и списку пользователей |
| `max_user.view`     | Просмотр карточки пользователя          |
| `max_user.edit`     | Редактирование профиля и контактов      |
| `max_user.delete`   | Удаление записи из локального реестра   |
| `max_user.manage`   | Действие «Обновить из MAX»              |

Для чатов действует та же схема с префиксом `max_chat`:

| Право чата          | Назначение                            |
|---------------------|---------------------------------------|
| `max_chat.view.any` | Доступ к разделу и списку чатов       |
| `max_chat.view`     | Просмотр карточки чата                |
| `max_chat.edit`     | Редактирование метаданных чата        |
| `max_chat.delete`   | Удаление записи из локального реестра |
| `max_chat.manage`   | Действие «Обновить из MAX»            |

Создание записей в контракт не входит: реестр наполняется вебхуками, ручное создание пакетом не поддерживается, поэтому
права `create` нет. Значения прав переопределяются в `config/laravel-max-client.php` / env `MAX_PERMISSION_*`.

В коде хоста права можно проверять и через политику: `$user->can('viewAny', MaxUser::class)`,
`$user->can('edit', $chat)`, `$user->can('manage', $userRecord)`. Политики регистрируются в сервис-провайдере
`laravel-max-client` для моделей из `users.model` / `chats.model`.

## Что показывается

В списках по умолчанию видны основные колонки; полный состав, включая скрытые, включается тулбаром переключения колонок.

### Пользователи

Имя, фамилия, отображаемое имя, описание, username, телефон, e-mail, аватар, отметка «бот», время последней активности и
отметка последней синхронизации профиля. Телефон приходит из контактов собеседника, поэтому рядом с номером
показывается, подтверждён он (`phone_verified_at`) или нет. Счётчик и список чатов — по связи `max_chat_users`.

### Чаты

Название (для группы и канала — `title` из MAX, для диалога — имя собеседника), тип, статус, иконка, ссылка, описание,
последняя активность, отметка последнего запроса метаданных и список участников чата с их статусом взаимодействия.

## Действия

### Изменить пользователя

Действие на странице просмотра и в строке списка пользователей открывает отдельную страницу редактирования. В локальной
БД (`max_users`) можно изменить имя, фамилию, никнейм, отображаемое имя, описание, телефон, e-mail и отметку «бот».
Служебные поля (`user_id`, аватары, `last_activity_time`, `phone_verified_at`, `profile_checked_at`) не редактируются:
их перезаписывает синхронизация с MAX.

### Удалить пользователя

Действие на странице просмотра и в строке списка пользователей. Удаляет запись из локального реестра (`max_users`)
вместе со строками `max_chat_users`: внешних ключей между таблицами нет, поэтому связи удаляются явно. Данные в самом
MAX не затрагиваются. Если на запись ссылается хост-приложение (например, внешний ключ `users.max_user_id`), транзакция
откатывается и плагин показывает уведомление об отказе.

### Обновить из MAX (пользователи)

Действие на странице просмотра пользователя. Вызывает `MaxUserProfileService::refresh()` из `laravel-max-client`.
Профиль обновляется, если у пользователя есть активный чат с ботом.

### Изменить чат

Действие на странице просмотра и в строке списка чатов открывает отдельную страницу редактирования. В локальной БД
(`max_chats`) можно изменить название, описание, ссылку и URL иконки. Служебные поля (`chat_id`, тип, статус, отметки
`last_activity_at` и `chat_checked_at`) не редактируются: статус и тип ведёт реестр, отметки — синхронизация с MAX.
Ссылка и иконка принимаются только с протоколом http/https.

### Обновить из MAX (чаты)

Действие на странице просмотра чата. Вызывает `MaxChatProfileService::sync($chatId)`: один запрос `getChat` заполняет
название, описание, ссылку и иконку чата и проставляет `chat_checked_at`. Это принудительное обновление конкретного
чата, поэтому свежая отметка `chat_checked_at` его не пропускает — в отличие от `refresh()`, который нужен расписанию
(`php artisan max:chats:refresh`).

### Удалить запись чата

Действие на странице просмотра и в строке списка чатов. Удаляет запись `MaxChat` из локального реестра (`max_chats`)
вместе со строками `max_chat_users` в транзакции: внешних ключей между таблицами нет, поэтому связи удаляются явно.
Данные в самом MAX не затрагиваются. Если на запись ссылается хост-приложение, транзакция откатывается, а плагин
показывает уведомление об отказе.

## Локализация

Пакет поставляет `lang/ru` и `lang/en` для подписей ресурсов, полей, фильтров, действий и уведомлений. Переопределить их
можно публикацией:

```bash
php artisan vendor:publish --tag=filament-max-users-lang
```

Подписи ресурсов в конфиге по умолчанию `null` и берутся из lang-файлов; значения в конфиге — только осознанный
override. Статусы чатов и fallback-название чата (`чат 123` / `chat 123`) приходят из `laravel-max-client::*`, поэтому
переопределяются публикацией `--tag=laravel-max-client-lang`. Даты выводятся форматом панели Filament по текущей локали,
жёсткие `d.m.Y` в плагине не заданы.

## История изменений

### v2.0.0

Большой релиз 2. Переход на `max-php-client` / `laravel-max-client` v2 (constraints `^2.0`, в ветке `new` — `dev-new`);
оба раздела стали управляемыми: страницы редактирования профиля/контактов пользователя и метаданных чата, удаление
локальных записей вместе со связями `max_chat_users` с откатом при ссылке из хост-приложения. Права и Policy-классы
перенесены в `laravel-max-client`: код `max_user` / `max_chat`, ability `view.any`, `view`, `edit`, `delete`, `manage`
(без `create` — реестр наполняется вебхуками); старые `users.*` / `chats.*` и `FILAMENT_MAX_USERS_PERMISSION_*` больше
не читаются, настройка прав — `MAX_PERMISSION_*`. Модели берутся из `laravel-max-client` (`users.model` /
`chats.model`), ключи `users_model` / `chats_model` из конфига плагина удалены. Подробности —
`.agents/release/RELEASE_NOTES_v2.0.0.md`.

### v1.1.1

Порядок и полный состав колонок в списках «Max пользователи» и «Max чаты» заданы явно, все колонки стали переключаемыми,
страницы просмотра перестроены по полям, русская подпись `username` стала «Никнейм». Действие «Обновить из MAX» у чатов
больше не падает на группах (участники приходят картой, фикс в `max-php-client` 1.1.9), зависимость `filament/filament`
поднята 5.7.8 → 5.9.0 (advisory CVE), ссылка чата рендерится только для http/https. Подробности —
`.agents/release/RELEASE_NOTES_v1.1.1.md`.

### v1.1.0

Переход на новую форму реестра `laravel-max-client` 1.2 (одна строка на чат, PK `chat_id`, связи в `max_chat_users`):
добавлены метаданные чата из MAX API, действие «Обновить из MAX» для чата через `MaxChatProfileService::sync()` и новое
право
`chats.manage`; в пользователях — телефон с отметкой подтверждения, e-mail, список чатов и фильтр «С телефоном». Релиз
ломающий для хостов на `laravel-max-client` 1.1.x: обязательны `php artisan migrate`, затем `php artisan max:upgrade`.
Подробности — `.agents/release/RELEASE_NOTES_v1.1.0.md`.

### v1.0.0

Первая публичная версия: два раздела Filament («Max пользователи» и «Max чаты») поверх реестра `laravel-max-client`,
действия обновления профиля и удаления записи чата, конфигурируемые права и модели.

## Локальная разработка

```bash
docker compose up -d --build
docker compose run --rm app composer install
docker compose exec -T app composer test          # PHPUnit
docker compose exec -T app composer analyse       # PHPStan level max
docker compose exec -T app composer lint          # php-cs-fixer --dry-run
docker compose exec -T app composer format        # php-cs-fixer fix
docker compose exec -T app composer coverage      # PHPUnit + порог покрытия ≥95% строк
docker compose exec -T app composer security-audit
```

Полный набор команд — Gate перед завершением задачи — описан в `AGENTS.md`; `composer coverage` требует драйвер покрытия
(`XDEBUG_MODE=coverage` в контейнере, `coverage: xdebug` в CI).

## Лицензия

MIT
