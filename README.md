# filament-max-users

Filament-плагин для **просмотра пользователей и чатов** MAX-мессенджера внутри Filament-панели.

Два раздела в одной navigation-группе «Max»:

- **Max пользователи** — список и профили пользователей `max_users`: телефон, e-mail, аватар, счётчик и список чатов,
  обновление профиля из MAX API.
- **Max чаты** — список и детали чатов `max_chats`: название, описание, ссылка, иконка из MAX API, участники чата.

> Данные — источник Bot API MAX. Редактирование/создание отсутствуют: плагин только показывает.

## Зависимости

- PHP ^8.4
- Laravel ^13
- Filament ^5
- `geekcodev/laravel-max-client` ^1.2 (реестр `max_users` / `max_chats` / `max_chat_users`)
- `geekcodev/max-php-client` ^1.1.8 (Bot API MAX)

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

Файл `config/filament-max-users.php`:

```php
return [
    'permissions' => [
        'users.view'   => env('FILAMENT_MAX_USERS_PERMISSION_USERS_VIEW', 'users.view'),
        'users.manage' => env('FILAMENT_MAX_USERS_PERMISSION_USERS_MANAGE', 'users.manage'),
        'chats.view'   => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_VIEW', 'chats.view'),
        'chats.manage' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_MANAGE', 'chats.manage'),
        'chats.delete' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_DELETE', 'chats.delete'),
    ],

    'users_model' => GeekCo\LaravelMaxClient\Models\MaxUser::class,
    'chats_model' => GeekCo\LaravelMaxClient\Models\MaxChat::class,

    'ui' => [
        'navigation_group' => env('FILAMENT_MAX_USERS_NAVIGATION_GROUP', 'Max'),
        'users' => [
            'navigation_icon'  => 'heroicon-o-users',
            'navigation_sort'  => 1,
            'navigation_label' => null,
            'label'            => 'Max пользователь',
            'plural_label'     => 'Max пользователи',
            'slug'             => 'max-users',
        ],
        'chats' => [
            'navigation_icon'  => 'heroicon-o-chat-bubble-left-right',
            'navigation_sort'  => 2,
            'navigation_label' => null,
            'label'            => 'Max чат',
            'plural_label'     => 'Max чаты',
            'slug'             => 'max-chats',
        ],
    ],
];
```

## Права доступа

Проверяются через `$user->can(...)` (совместимо со spatie/laravel-permission и Gate):

| Право          | Описание                                     |
|----------------|----------------------------------------------|
| `users.view`   | Доступ к разделу «Max пользователи»          |
| `users.manage` | Действие «Обновить из MAX»                   |
| `chats.view`   | Доступ к разделу «Max чаты»                  |
| `chats.manage` | Действие «Обновить из MAX» (метаданные чата) |
| `chats.delete` | Удаление записи чата из локального реестра   |

## Что показывается

В списках по умолчанию видны основные колонки; полный состав, включая скрытые, включается тулбаром переключения
колонок.

### Пользователи

Имя, фамилия, отображаемое имя, описание, username, телефон, e-mail, аватар, отметка «бот», время последней активности и
отметка последней синхронизации профиля. Телефон приходит из контактов собеседника, поэтому рядом с номером
показывается, подтверждён он (`phone_verified_at`) или нет. Счётчик и список чатов — по связи `max_chat_users`.

### Чаты

Название (для группы и канала — `title` из MAX, для диалога — имя собеседника), тип, статус, иконка, ссылка, описание,
последняя активность, отметка последнего запроса метаданных и список участников чата с их статусом взаимодействия.

## Действия

### Обновить из MAX (пользователи)

Действие на странице просмотра пользователя. Вызывает `MaxUserProfileService::refresh()` из `laravel-max-client`.
Профиль обновляется, если у пользователя есть активный чат с ботом.

### Обновить из MAX (чаты)

Действие на странице просмотра чата. Вызывает `MaxChatProfileService::sync($chatId)`: один запрос `getChat` заполняет
название, описание, ссылку и иконку чата и проставляет `chat_checked_at`. Это принудительное обновление конкретного
чата, поэтому свежая отметка `chat_checked_at` его не пропускает — в отличие от `refresh()`, который нужен расписанию
(`php artisan max:chats:refresh`).

### Удалить запись чата

Действие на странице просмотра чата. Удаляет запись `MaxChat` из локального реестра (`max_chats`) вместе со строками
`max_chat_users`: внешних ключей между таблицами нет, поэтому связи удаляются явно. Данные в самом MAX не затрагиваются.

## История изменений

### v1.1.1

Порядок и полный состав колонок в списках «Max пользователи» и «Max чаты» заданы явно, все колонки стали
переключаемыми, страницы просмотра перестроены по полям, русская подпись `username` стала «Никнейм». Действие
«Обновить из MAX» у чатов больше не падает на группах (участники приходят картой, фикс в `max-php-client` 1.1.9),
зависимость `filament/filament` поднята 5.7.8 → 5.9.0 (advisory CVE), ссылка чата рендерится только для http/https.
Подробности — `.agents/release/RELEASE_NOTES_v1.1.1.md`.

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
