# v2.0.0

Синхронизация с ядром `geekcodev/max-php-client` **v2.0.0** и адаптером `geekcodev/laravel-max-client` **v2.0.0**. Код
пакета не менялся: плагин работает с реестром чатов/пользователей и DTO профилей, типизированные события MAX его не
касаются.

## Изменение (BC)

- Constraints: `geekcodev/max-php-client` `^1.1.8` → `^2.0`, `geekcodev/laravel-max-client` `^1.2.0` → `^2.0`
  (предрелизная ветка `new` — `dev-new`).
- Миграций, ключей конфига и публичных методов пакета изменения не касаются.

## Качество

Lint 0/22, PHPStan level max 0, PHPUnit 54/54 (187 assertions), покрытие 97.47% строк (501/514), `composer audit` 0.
