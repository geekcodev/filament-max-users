<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Resources\Actions\DeleteMaxUserAction;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\FilamentMaxUsers\Support\UserPresenter;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\LaravelMaxClient\Services\MaxUserProfileService;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class ViewMaxUser extends ViewRecord
{
    protected static string $resource = MaxUserResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('user_id')
                    ->label(__('filament-max-users::users.view.user_id')),
                IconEntry::make('is_bot')
                    ->label(__('filament-max-users::users.view.is_bot'))
                    ->boolean(),
                ImageEntry::make('avatar_url')
                    ->label(__('filament-max-users::users.view.avatar_url'))
                    ->circular(),
                TextEntry::make('username')
                    ->label(__('filament-max-users::users.view.username')),
                TextEntry::make('first_name')
                    ->label(__('filament-max-users::users.view.first_name')),
                TextEntry::make('last_name')
                    ->label(__('filament-max-users::users.view.last_name')),
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
                    ->dateTime()
                    ->placeholder(__('filament-max-users::users.view.phone_unverified')),
                TextEntry::make('email')
                    ->label(__('filament-max-users::users.view.email')),
                TextEntry::make('last_activity_time')
                    ->label(__('filament-max-users::users.view.last_activity_time'))
                    ->getStateUsing(static fn (Model $record): ?Carbon => UserPresenter::lastActivityAt($record))
                    ->dateTime(),
                TextEntry::make('profile_checked_at')
                    ->label(__('filament-max-users::users.view.profile_checked_at'))
                    ->dateTime()
                    ->placeholder(__('filament-max-users::users.view.never_checked')),
                RepeatableEntry::make('maxChats')
                    ->label(__('filament-max-users::users.view.chats'))
                    ->schema([
                        TextEntry::make('displayName')
                            ->label(__('filament-max-users::users.view.chat_name'))
                            ->getStateUsing(static fn (Model $record): string => ChatPresenter::displayName($record))
                            ->url(static fn (Model $record): string => MaxChatResource::getUrl('view', ['record' => $record])),
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
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->authorize(static fn (Model $record): bool => Gate::allows('edit', $record)),
            Action::make('refresh')
                ->label(__('filament-max-users::users.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading(__('filament-max-users::users.actions.refresh_heading'))
                ->modalDescription(__('filament-max-users::users.actions.refresh_description'))
                ->modalSubmitActionLabel(__('filament-max-users::users.actions.refresh_submit'))
                ->authorize(static fn (Model $record): bool => Gate::allows('manage', $record))
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
            DeleteMaxUserAction::make(),
        ];
    }
}
