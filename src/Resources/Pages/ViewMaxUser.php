<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\LaravelMaxClient\Services\MaxUserProfileService;

class ViewMaxUser extends ViewRecord
{
    protected static string $resource = MaxUserResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ImageEntry::make('avatar_url')
                    ->label(__('filament-max-users::users.view.avatar_url'))
                    ->circular(),
                TextEntry::make('user_id')
                    ->label(__('filament-max-users::users.view.user_id')),
                TextEntry::make('first_name')
                    ->label(__('filament-max-users::users.view.first_name')),
                TextEntry::make('last_name')
                    ->label(__('filament-max-users::users.view.last_name')),
                TextEntry::make('username')
                    ->label(__('filament-max-users::users.view.username')),
                IconEntry::make('is_bot')
                    ->label(__('filament-max-users::users.view.is_bot'))
                    ->boolean(),
                TextEntry::make('last_activity_time')
                    ->label(__('filament-max-users::users.view.last_activity_time'))
                    ->dateTime('d.m.Y H:i:s'),
                TextEntry::make('name')
                    ->label(__('filament-max-users::users.view.name')),
                TextEntry::make('description')
                    ->label(__('filament-max-users::users.view.description')),
                TextEntry::make('phone')
                    ->label(__('filament-max-users::users.view.phone')),
                TextEntry::make('email')
                    ->label(__('filament-max-users::users.view.email')),
                TextEntry::make('profile_checked_at')
                    ->label(__('filament-max-users::users.view.profile_checked_at'))
                    ->dateTime('d.m.Y H:i:s'),
                TextEntry::make('max_chats_count')
                    ->label(__('filament-max-users::users.view.chats_count'))
                    ->counts('maxChats'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $managePermission = config()->string('filament-max-users.permissions.users.manage', 'users.manage');

        return [
            Action::make('refresh')
                ->label(__('filament-max-users::users.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading(__('filament-max-users::users.actions.refresh_heading'))
                ->modalDescription(__('filament-max-users::users.actions.refresh_description'))
                ->modalSubmitActionLabel(__('filament-max-users::users.actions.refresh_submit'))
                ->authorize($managePermission)
                ->action(function (): void {
                    /** @var MaxUser $record */
                    $record = $this->getRecord();

                    $updated = app(MaxUserProfileService::class)->refresh($record->user_id);

                    if ($updated) {
                        $this->refreshFormData([
                            'first_name', 'last_name', 'username', 'is_bot',
                            'last_activity_time', 'name', 'description',
                            'avatar_url', 'full_avatar_url', 'phone', 'email',
                            'profile_checked_at',
                        ]);

                        Notification::make()
                            ->title(__('filament-max-users::users.actions.refresh_success'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('filament-max-users::users.actions.refresh_failure'))
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
