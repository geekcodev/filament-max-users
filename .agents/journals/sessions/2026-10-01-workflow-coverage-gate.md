---
tags: [ workflow, agents, coverage, ci, docs, gate ]
date: 2026-10-01
---

# Workflow-перенос, гейт покрытия и аудит ветки

## Проблема

`AGENTS.md` плагина (190 строк) разошёлся с `AGENTS.md` соседнего `laravel-max-client` (533 строки): не хватало рабочего
журнала в едином месте, gotchas, чек-листа и явных правил git. Попутно выяснилось, что `composer test` не строит отчёт о
покрытии — падение покрытия было бы незаметным, а в CI драйвера покрытия не было вовсе. В конце сессии — аудит всех
изменений ветки на соответствие `AGENTS.md`.

## Решение

- `AGENTS.md` переработан: динамическая сверка версий (`composer show`, `git tag`), правило «регрессия в интеграционном
  приложении чинится здесь», текст коммита на английском по Conventional Commits по всему diff ветки, запрет
  `git add .`, таблица «что куда писать», соглашения с BC-совместимостью, разделы «Частые ошибки» и «Чек-лист», Gate с
  покрытием; форматы рабочей памяти закреплены в §4.1.
- Гейт покрытия: `scripts/check-coverage.php` (порог 95% строк), скрипт `composer coverage`, в CI `coverage: xdebug`;
  три теста на `ChatPresenter` для модели хоста.
- `phpstan.neon`: `reportUnmatchedIgnoredErrors: false` — без `composer.lock` CI получает более новые Filament/Livewire/
  Larastan, хелперных ошибок там не возникает, и незакрытые записи baseline роняли `composer analyse`.
- `README.md`: локальная разработка (`composer coverage`, `-T`) и «История изменений».
- Рабочая память: `.ai/progress/` → `.agents/{plans,release,journals}`, release notes → `RELEASE_NOTES_vX.Y.Z.md`,
  `.gitattributes` с `export-ignore` для `.agents/**`, `tests/**`, `.github/**` и dev-конфигов.
- Аудит ветки: `canCreate()`/`canEdit()`/`canDelete()` → `false` с двумя feature-тестами; контракт `counts()` в §5 и
  gotcha 3 приведён к коду (`counts('chatLinks')`, `counts('chatUsers')` — Filament v5 ждёт имя связи); выравнивание
  `scripts` в `composer.json`; BC-строка про тексты подписей.
- Release notes v1.1.0: релиз ломающий (`laravel-max-client ^1.2.0`, новая форма реестра, `max:upgrade`, право
  `chats.manage`), поэтому версия minor.

Ключевые решения: порог 95% строк взят как в соседнем пакете, но сначала закрыты тестами непокрытые ветки
`ChatPresenter`
(иначе гейт проходил бы впритык, 95.66%); `.agents/` оставлен в git — иначе после `git clone` план и журнал недоступны,
из дистрибутива каталог исключён через `export-ignore`; дрейф зависимостей вместо правки baseline снят флагом
`reportUnmatchedIgnoredErrors`, потому что baseline содержит только хелперы из `tests/`.

## Тесты

35 тестов / 123 утверждения, все зелёные. Покрытие строк 97.22% при пороге 95%.

## Нюансы

- Клиент-специфичные разделы соседнего `AGENTS.md` (webhook, rate limits, Long Polling, `max:listen`) не переносились: в
  Filament-плагине их нет. Взят только workflow и структура.
- Ловушка `.gitattributes`: нужен паттерн `/.agents/**` — с завершающим слешем `git check-attr export-ignore` молча
  отдаёт `unspecified`.
- Локальный Gate не воспроизводит CI: на своём lock хелперные ошибки есть и baseline совпадает. Проверка в условиях CI —
  копия дерева без `composer.lock` и Gate в ней (gotcha 14, шаг 35 плана).
- Изменены тексты подписей (не ключи), включая русский текст в `lang/en` — BC-строка это допускает.

## Gate

Локально: lint 0 · phpstan max 0 · phpunit 35/35 (123) · покрытие 97.22% · audit 0. В условиях CI (Filament 5.9.0,
Livewire 4.4.7, Larastan 3.12.2, PHPStan 2.2.16) — тот же результат по всем пяти шагам.