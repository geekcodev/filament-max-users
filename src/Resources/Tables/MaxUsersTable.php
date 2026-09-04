<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaxUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label(__('filament-max-users::users.table.user_id'))
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label(__('filament-max-users::users.table.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('filament-max-users::users.table.last_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('username')
                    ->label(__('filament-max-users::users.table.username'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_bot')
                    ->label(__('filament-max-users::users.table.is_bot'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->formatStateUsing(
                        fn (bool $state): string => $state
                            ? __('filament-max-users::users.table.filter_is_bot_bot')
                            : __('filament-max-users::users.table.filter_is_bot_user'),
                    ),
                TextColumn::make('last_activity_time')
                    ->label(__('filament-max-users::users.table.last_activity_time'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_chats_count')
                    ->label(__('filament-max-users::users.table.chats_count'))
                    ->counts('maxChats')
                    ->sortable(),
                ImageColumn::make('avatar_url')
                    ->label(__('filament-max-users::users.table.avatar'))
                    ->circular()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_bot')
                    ->label(__('filament-max-users::users.table.filter_is_bot'))
                    ->options([
                        false => __('filament-max-users::users.table.filter_is_bot_user'),
                        true => __('filament-max-users::users.table.filter_is_bot_bot'),
                    ]),
            ])
            ->defaultSort('user_id', 'desc');
    }
}
