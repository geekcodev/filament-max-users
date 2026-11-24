<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use GeekCo\LaravelMaxClient\Services\MaxChatProfileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class RefreshMaxChatAction
{
    public static function make(): Action
    {
        return Action::make('refreshChat')
            ->label(__('filament-max-users::chats.actions.refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading(__('filament-max-users::chats.actions.refresh_heading'))
            ->modalDescription(__('filament-max-users::chats.actions.refresh_description'))
            ->modalSubmitActionLabel(__('filament-max-users::chats.actions.refresh_submit'))
            ->authorize(static fn (Model $record): bool => Gate::allows('manage', $record))
            ->action(static function (Model $record): void {
                $id = $record->getKey();
                $updated = app(MaxChatProfileService::class)->sync(\is_numeric($id) ? (int) $id : 0);

                if ($updated) {
                    $record->refresh();

                    Notification::make()
                        ->title(__('filament-max-users::chats.actions.refresh_success'))
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title(__('filament-max-users::chats.actions.refresh_failure'))
                        ->danger()
                        ->send();
                }
            });
    }
}
