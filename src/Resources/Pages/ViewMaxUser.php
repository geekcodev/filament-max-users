<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\FilamentMaxUsers\Support\UserPresenter;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\LaravelMaxClient\Services\MaxUserProfileService;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;

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
                TextEntry::make('name')
                    ->label(__('filament-max-users::users.view.name')),
                TextEntry::make('description')
                    ->label(__('filament-max-users::users.view.description'))
                    ->columnSpanFull(),
                TextEntry::make('phone')
                    ->label(__('filament-max-users::users.view.phone'))
                    ->icon(fn (Model $record): ?string => UserPresenter::phoneIcon($record))
                    ->tooltip(UserPresenter::phoneTooltip()),
                TextEntry::make('phone_verified_at')
                    ->label(__('filament-max-users::users.view.phone_verified_at'))
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder(__('filament-max-users::users.view.phone_unverified')),
                TextEntry::make('email')
                    ->label(__('filament-max-users::users.view.email')),
                TextEntry::make('last_activity_time')
                    ->label(__('filament-max-users::users.view.last_activity_time'))
                    ->dateTime('d.m.Y H:i:s'),
                TextEntry::make('profile_checked_at')
                    ->label(__('filament-max-users::users.view.profile_checked_at'))
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder(__('filament-max-users::users.view.never_checked')),
                RepeatableEntry::make('maxChats')
                    ->label(__('filament-max-users::users.view.chats'))
                    ->schema([
                        TextEntry::make('displayName')
                            ->label(__('filament-max-users::users.view.chat_name'))
                            ->getStateUsing(static fn (Model $record): string => ChatPresenter::displayName($record)),
                        TextEntry::make('chat_id')
                            ->label(__('filament-max-users::users.view.chat_id')),
                        TextEntry::make('chat_type')
                            ->label(__('filament-max-users::users.view.chat_type'))
                            ->badge()
                            ->color(static fn (?ChatType $state): string => match ($state) {
                                ChatType::Dialog => 'info',
                                ChatType::Chat => 'success',
                                ChatType::Channel => 'warning',
                                null => 'gray',
                            })
                            ->formatStateUsing(static fn (?ChatType $state): string => match ($state) {
                                ChatType::Dialog => __('filament-max-users::chats.chat_type.dialog'),
                                ChatType::Chat => __('filament-max-users::chats.chat_type.group'),
                                ChatType::Channel => __('filament-max-users::chats.chat_type.channel'),
                                null => __('filament-max-users::chats.chat_type.unknown'),
                            }),
                        TextEntry::make('last_activity_at')
                            ->label(__('filament-max-users::users.view.chat_last_activity_at'))
                            ->dateTime('d.m.Y H:i:s'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
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
