# filament-max-users

Filament-плагин для **просмотра пользователей и чатов** MAX-мессенджера внутри Filament-панели.

Два раздела в одной navigation-группе «Max»:

- **Max пользователи** — список и профили пользователей `max_users`, счётчик чатов, обновление профиля из MAX API.
- **Max чаты** — список и детали чатов `max_chats`, данные реестра + связанный пользователь.

> Данные — источник Bot API MAX. Редактирование/создание отсутствуют: плагин только показывает.

## Зависимости

- PHP ^8.4
- Laravel ^13
- Filament ^5
- `geekcodev/laravel-max-client` ^1.1 (реестр `max_users` / `max_chats`)
- `geekcodev/max-php-client` ^1.0 (Bot API MAX)

## Установка

```bash
composer require geekcodev/filament-max-users
```

Опубликуйте миграции и модель из `laravel-max-client`:

```bash
php artisan vendor:publish --tag=laravel-max-client-migrations
php artisan migrate
```

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
        'chats.delete' => env('FILAMENT_MAX_USERS_PERMISSION_CHATS_DELETE', 'chats.delete'),
    ],

    'users_model' => GeekCo\LaravelMaxClient\Models\MaxUser::class,
    'chats_model' => GeekCo\LaravelMaxClient\Models\MaxChat::class,

    'ui' => [
        'navigation_group' => 'Max',
        'users' => [
            'navigation_icon' => 'heroicon-o-users',
            'navigation_sort' => 1,
            'slug' => 'max-users',
        ],
        'chats' => [
            'navigation_icon' => 'heroicon-o-chat-bubble-left-right',
            'navigation_sort' => 2,
            'slug' => 'max-chats',
        ],
    ],
];
```

## Права доступа

Проверяются через `$user->can(...)` (совместимо со spatie/laravel-permission и Gate):

| Право          | Описание                                   |
|----------------|--------------------------------------------|
| `users.view`   | Доступ к разделу «Max пользователи»        |
| `users.manage` | Действие «Обновить из MAX»                 |
| `chats.view`   | Доступ к разделу «Max чаты»                |
| `chats.delete` | Удаление записи чата из локального реестра |

## Действия

### Обновить из MAX (пользователи)

Действие на странице просмотра пользователя. Вызывает `MaxUserProfileService::refresh()` из `laravel-max-client`.
Профиль обновляется, если у пользователя есть активный чат с ботом.

### Удалить запись чата

Действие на странице просмотра чата. Удаляет только запись `MaxChat` из локального реестра (`max_chats`). Данные в самом
MAX не затрагиваются.

## Локальная разработка

```bash
docker compose up -d --build
docker compose run --rm app composer install
docker compose exec app composer test       # PHPUnit
docker compose exec app composer analyse    # PHPStan level max
docker compose exec app composer lint       # php-cs-fixer --dry-run
docker compose exec app composer format     # php-cs-fixer fix
docker compose exec app composer security-audit
```

## Лицензия

MIT
