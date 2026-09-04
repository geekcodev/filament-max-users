<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\MaxPhpClient\Enum\ChatType;

class MaxChatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('filament-max-users::chats.table.id'))
                    ->sortable(),
                TextColumn::make('chat_id')
                    ->label(__('filament-max-users::chats.table.chat_id'))
                    ->sortable(),
                TextColumn::make('chat_type')
                    ->label(__('filament-max-users::chats.table.chat_type'))
                    ->badge()
                    ->color(fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => 'info',
                        ChatType::Chat => 'success',
                        ChatType::Channel => 'warning',
                        null => 'gray',
                    })
                    ->formatStateUsing(fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => __('filament-max-users::chats.chat_type.dialog'),
                        ChatType::Chat => __('filament-max-users::chats.chat_type.group'),
                        ChatType::Channel => __('filament-max-users::chats.chat_type.channel'),
                        null => __('filament-max-users::chats.chat_type.unknown'),
                    }),
                TextColumn::make('maxUser.first_name')
                    ->label(__('filament-max-users::chats.table.user_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label(__('filament-max-users::chats.table.user_id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('filament-max-users::chats.table.status'))
                    ->badge()
                    ->color(fn (MaxChatStatus $state): string => match ($state) {
                        MaxChatStatus::Active => 'success',
                        MaxChatStatus::Stopped => 'warning',
                        MaxChatStatus::Removed => 'danger',
                    })
                    ->formatStateUsing(fn (MaxChatStatus $state): string => $state->label()),
                TextColumn::make('last_activity_at')
                    ->label(__('filament-max-users::chats.table.last_activity_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
