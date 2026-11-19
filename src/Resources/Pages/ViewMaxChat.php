<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Resources\Actions\DeleteMaxChatAction;
use GeekCo\FilamentMaxUsers\Resources\Actions\RefreshMaxChatAction;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ViewMaxChat extends ViewRecord
{
    protected static string $resource = MaxChatResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('chat_id')
                    ->label(__('filament-max-users::chats.view.chat_id')),
                ImageEntry::make('icon_url')
                    ->label(__('filament-max-users::chats.view.icon_url'))
                    ->circular(),
                TextEntry::make('chat_type')
                    ->label(__('filament-max-users::chats.view.chat_type'))
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
                TextEntry::make('status')
                    ->label(__('filament-max-users::chats.view.status'))
                    ->badge()
                    ->color(static fn (MaxChatStatus $state): string => match ($state) {
                        MaxChatStatus::Active => 'success',
                        MaxChatStatus::Stopped => 'warning',
                        MaxChatStatus::Removed => 'danger',
                    })
                    ->formatStateUsing(static fn (MaxChatStatus $state): string => $state->label()),
                TextEntry::make('displayName')
                    ->label(__('filament-max-users::chats.view.display_name'))
                    ->getStateUsing(static fn (Model $record): string => ChatPresenter::displayName($record)),
                TextEntry::make('description')
                    ->label(__('filament-max-users::chats.view.description'))
                    ->placeholder(__('filament-max-users::chats.title.unknown'))
                    ->columnSpanFull(),
                TextEntry::make('link')
                    ->label(__('filament-max-users::chats.view.link'))
                    ->url(static fn (?string $state): ?string => ChatPresenter::safeUrl($state))
                    ->openUrlInNewTab()
                    ->placeholder(__('filament-max-users::chats.title.unknown')),
                TextEntry::make('last_activity_at')
                    ->label(__('filament-max-users::chats.view.last_activity_at'))
                    ->dateTime(),
                TextEntry::make('chat_checked_at')
                    ->label(__('filament-max-users::chats.view.chat_checked_at'))
                    ->dateTime()
                    ->placeholder(__('filament-max-users::chats.title.never_checked')),
                RepeatableEntry::make('chatUsers')
                    ->label(__('filament-max-users::chats.view.users'))
                    ->schema([
                        TextEntry::make('maxUser.first_name')
                            ->label(__('filament-max-users::chats.view.user_first_name')),
                        TextEntry::make('maxUser.last_name')
                            ->label(__('filament-max-users::chats.view.user_last_name')),
                        TextEntry::make('user_id')
                            ->label(__('filament-max-users::chats.view.user_id')),
                        TextEntry::make('status')
                            ->label(__('filament-max-users::chats.view.user_status'))
                            ->badge()
                            ->color(static fn (MaxChatStatus $state): string => match ($state) {
                                MaxChatStatus::Active => 'success',
                                MaxChatStatus::Stopped => 'warning',
                                MaxChatStatus::Removed => 'danger',
                            })
                            ->formatStateUsing(static fn (MaxChatStatus $state): string => $state->label()),
                        TextEntry::make('last_activity_at')
                            ->label(__('filament-max-users::chats.view.user_last_activity_at'))
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
            RefreshMaxChatAction::make(),
            DeleteMaxChatAction::make(),
        ];
    }
}
