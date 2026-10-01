# v1.0.0 — Filament-плагин для просмотра пользователей и чатов MAX-мессенджера

## Новое

**Раздел «Max пользователи»** (`MaxUserResource`):

- Список пользователей с фильтрацией по типу (пользователь/бот).
- Просмотр профиля: все поля `max_users`, количество чатов (счётчик через связь `maxChats`).
- Действие «Обновить из MAX» — обновление профиля через `MaxUserProfileService::refresh()`.

**Раздел «Max чаты»** (`MaxChatResource`):

- Список чатов со статусом (активен/остановлен/удалён), типом (диалог/группа/канал) и связанным пользователем.
- Просмотр деталей чата: данные реестра `max_chats`, включая тип чата.
- Действие удаления записи чата из локального реестра.

Общее:

- Оба раздела в одной navigation-группе «Max».
- Конфигурируемые права доступа (`users.view`, `users.manage`, `chats.view`, `chats.delete`).
- Конфигурируемые модели (по умолчанию из `laravel-max-client`).
- Publishable конфиг и языковые файлы.
- PHPStan level max, php-cs-fixer PSR-12, PHPUnit.

## Затронутые сценарии

Установка и подключение к панели:

```bash
composer require geekcodev/filament-max-users
php artisan vendor:publish --tag=laravel-max-client-migrations
php artisan migrate
php artisan vendor:publish --tag=filament-max-users-config
```

```php
->plugin(\GeekCo\FilamentMaxUsers\FilamentMaxUsersPlugin::make())
```

## Качество

- PHP ^8.4, Laravel ^13, Filament ^5.
- Требуются `geekcodev/laravel-max-client` ^1.1.1 и `geekcodev/max-php-client` ^1.0.
- PHPStan level max — 0 ошибок, php-cs-fixer (PSR-12) — 0 правок.
- PHPUnit: 12 feature-тестов, все зелёные; `composer audit` — без уязвимостей.
