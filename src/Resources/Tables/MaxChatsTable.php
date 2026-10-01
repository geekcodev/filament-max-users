<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;

class MaxChatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon_url')
                    ->label(__('filament-max-users::chats.table.icon'))
                    ->circular()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('displayName')
                    ->label(__('filament-max-users::chats.table.display_name'))
                    ->getStateUsing(static fn (Model $record): string => ChatPresenter::displayName($record))
                    ->searchable(['title']),
                TextColumn::make('chat_id')
                    ->label(__('filament-max-users::chats.table.chat_id'))
                    ->sortable(),
                TextColumn::make('chat_type')
                    ->label(__('filament-max-users::chats.table.chat_type'))
                    ->badge()
                    ->sortable()
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
                TextColumn::make('chat_users_count')
                    ->label(__('filament-max-users::chats.table.users_count'))
                    ->counts('chatUsers')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament-max-users::chats.table.status'))
                    ->badge()
                    ->sortable()
                    ->color(static fn (MaxChatStatus $state): string => match ($state) {
                        MaxChatStatus::Active => 'success',
                        MaxChatStatus::Stopped => 'warning',
                        MaxChatStatus::Removed => 'danger',
                    })
                    ->formatStateUsing(static fn (MaxChatStatus $state): string => $state->label()),
                TextColumn::make('last_activity_at')
                    ->label(__('filament-max-users::chats.table.last_activity_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('filament-max-users::chats.table.title'))
                    ->sortable()
                    ->placeholder(__('filament-max-users::chats.title.unknown'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chat_checked_at')
                    ->label(__('filament-max-users::chats.table.chat_checked_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('chat_type')
                    ->label(__('filament-max-users::chats.table.filter_chat_type'))
                    ->options([
                        ChatType::Dialog->value => __('filament-max-users::chats.chat_type.dialog'),
                        ChatType::Chat->value => __('filament-max-users::chats.chat_type.group'),
                        ChatType::Channel->value => __('filament-max-users::chats.chat_type.channel'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('filament-max-users::chats.table.filter_status'))
                    ->options([
                        MaxChatStatus::Active->value => MaxChatStatus::Active->label(),
                        MaxChatStatus::Stopped->value => MaxChatStatus::Stopped->label(),
                        MaxChatStatus::Removed->value => MaxChatStatus::Removed->label(),
                    ]),
            ])
            ->defaultSort('chat_id', 'desc');
    }
}
