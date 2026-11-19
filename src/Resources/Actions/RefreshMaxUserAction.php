<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\LaravelMaxClient\Services\MaxUserProfileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class RefreshMaxUserAction
{
    public static function make(): Action
    {
        return Action::make('refresh')
            ->label(__('filament-max-users::users.actions.refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading(__('filament-max-users::users.actions.refresh_heading'))
            ->modalDescription(__('filament-max-users::users.actions.refresh_description'))
            ->modalSubmitActionLabel(__('filament-max-users::users.actions.refresh_submit'))
            ->authorize(static fn (Model $record): bool => Gate::allows('manage', $record))
            ->action(static function (Model $record, mixed $livewire): void {
                /** @var MaxUser $record */
                $id = $record->getKey();
                $updated = app(MaxUserProfileService::class)->refresh(\is_numeric($id) ? (int) $id : 0);

                if (! $updated) {
                    Notification::make()
                        ->title(__('filament-max-users::users.actions.refresh_failure'))
                        ->danger()
                        ->send();

                    return;
                }

                $record->refresh();

                if ($livewire instanceof ViewRecord) {
                    $livewire->refreshFormData([
                        'first_name', 'last_name', 'username', 'is_bot',
                        'last_activity_time', 'name', 'description',
                        'avatar_url', 'full_avatar_url', 'phone', 'email',
                        'profile_checked_at',
                    ]);
                }

                Notification::make()
                    ->title(__('filament-max-users::users.actions.refresh_success'))
                    ->success()
                    ->send();
            });
    }
}
