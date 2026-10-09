<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Support;

use GeekCo\LaravelMaxClient\Models\MaxChat;
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

        return \is_scalar($key) ? 'chat ' . $key : 'chat';
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

    /**
     * Есть ли у записи метаданные, полученные через getChat.
     */
    public static function hasMetadata(Model $record): bool
    {
        if ($record instanceof MaxChat) {
            return $record->title !== null && $record->title !== '';
        }

        $title = $record->getAttribute('title');

        return \is_string($title) && $title !== '';
    }
}
