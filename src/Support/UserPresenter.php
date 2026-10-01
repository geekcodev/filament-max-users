<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Подписи пользователей, зависящие от состояния записи реестра.
 *
 * Модель пользователя в хосте переопределяется через конфиг, поэтому атрибуты
 * читаются как у произвольного Model, а не через типизированные свойства.
 */
final class UserPresenter
{
    /**
     * Иконка телефона: номер из подтверждённого контакта — галочка, номер без
     * отметки подтверждения — вопросительный знак, телефона нет — без иконки.
     */
    public static function phoneIcon(Model $record): ?string
    {
        $phone = $record->getAttribute('phone');

        if (! \is_string($phone) || $phone === '') {
            return null;
        }

        return $record->getAttribute('phone_verified_at') === null
            ? 'heroicon-m-question-mark-circle'
            : 'heroicon-m-check-badge';
    }

    /**
     * Телефон приходит из контактов того, кто писал боту, поэтому сам по себе
     * номер ничего не говорит о его достоверности.
     */
    public static function phoneTooltip(): string
    {
        return (string) __('filament-max-users::users.table.phone_hint');
    }
}
