<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Support;

use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;

/**
 * Подписи чатов, зависящие от состояния записи реестра.
 *
 * Смысл вынесен сюда, потому что таблица и страница просмотра показывают одно и
 * то же, а модель чата в хосте переопределяется через конфиг: у произвольного
 * подкласса MaxChat название всегда есть, у посторонней модели — нет.
 */
final class ChatPresenter
{
    /**
     * Название чата для интерфейса: у группы и канала — title из getChat, у
     * диалога — имя собеседника, иначе строка с идентификатором.
     */
    public static function displayName(Model $record): string
    {
        if ($record instanceof MaxChat) {
            return $record->displayName();
        }

        $title = $record->getAttribute('title');

        if (\is_string($title) && $title !== '') {
            return $title;
        }

        $key = $record->getKey();

        if (! \is_scalar($key)) {
            $unknown = __('laravel-max-client::chat.unknown');

            return \is_string($unknown) ? $unknown : 'chat';
        }

        $fallback = __('laravel-max-client::chat.fallback', ['id' => $key]);

        return \is_string($fallback) ? $fallback : 'chat '.$key;
    }

    /**
     * Диалог в MAX не имеет own-метаданных: название берётся из профиля
     * собеседника. Атрибут может быть enum или строкой у переопределённой модели.
     */
    public static function isDialog(Model $record): bool
    {
        $type = $record->getAttribute('chat_type');

        return $type === ChatType::Dialog || $type === ChatType::Dialog->value;
    }

    /**
     * Ссылка чата для href: только http/https, иначе null.
     *
     * Значение приходит из MAX API и уходит в href без дополнительной проверки,
     * поэтому схему фильтруем здесь: javascript:/data: стали бы XSS при клике.
     */
    public static function safeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $trimmed = \trim($url);
        $scheme = \parse_url($trimmed, \PHP_URL_SCHEME);

        if (!\is_string($scheme)) {
            return null;
        }

        return \in_array(\strtolower($scheme), ['http', 'https'], true) ? $trimmed : null;
    }
}
