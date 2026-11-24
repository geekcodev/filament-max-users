<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Actions;

use Filament\Actions\DeleteAction;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\LaravelMaxClient\Support\Config as MaxClientConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Удаление записи пользователя из локального реестра max_users вместе со
 * строками max_chat_users: внешних ключей между таблицами нет, поэтому связи
 * удаляются явно, иначе они остались бы висеть без пользователя.
 *
 * Если на запись ссылается хост-приложение (внешний ключ на max_users),
 * транзакция откатывается, действие показывает неудачу и не оставляет
 * частично удалённых связей.
 */
final class DeleteMaxUserAction
{
    public static function make(): DeleteAction
    {
        return DeleteAction::make('delete')
            ->label(__('filament-max-users::users.actions.delete'))
            ->icon('heroicon-o-trash')
            ->modalHeading(__('filament-max-users::users.actions.delete_heading'))
            ->modalDescription(__('filament-max-users::users.actions.delete_description'))
            ->modalSubmitActionLabel(__('filament-max-users::users.actions.delete_submit'))
            ->authorize(static fn (Model $record): bool => Gate::allows('delete', $record))
            ->using(static function (Model $record): bool {
                try {
                    DB::transaction(static function () use ($record): void {
                        app(MaxClientConfig::class)->chatUsersModel()::query()
                            ->where('user_id', $record->getKey())
                            ->delete();

                        $record->delete();
                    });
                } catch (QueryException $exception) {
                    report($exception);

                    return false;
                }

                return true;
            })
            ->successNotificationTitle(__('filament-max-users::users.actions.delete_success'))
            ->failureNotificationTitle(__('filament-max-users::users.actions.delete_failure'))
            ->successRedirectUrl(static fn (): string => MaxUserResource::getUrl('index'));
    }
}
