<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use GeekCo\FilamentMaxUsers\Support\UserPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MaxUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label(__('filament-max-users::users.table.user_id'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_bot')
                    ->label(__('filament-max-users::users.table.is_bot'))
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->color(static fn (bool $state): string => $state ? 'warning' : 'success')
                    ->formatStateUsing(
                        static fn (bool $state): string => $state
                            ? __('filament-max-users::users.table.filter_is_bot_bot')
                            : __('filament-max-users::users.table.filter_is_bot_user'),
                    ),
                ImageColumn::make('avatar_url')
                    ->label(__('filament-max-users::users.table.avatar'))
                    ->circular()
                    ->limit(30)
                    ->toggleable(),
                ImageColumn::make('full_avatar_url')
                    ->label(__('filament-max-users::users.table.full_avatar_url'))
                    ->circular()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('first_name')
                    ->label(__('filament-max-users::users.table.first_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_name')
                    ->label(__('filament-max-users::users.table.last_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(__('filament-max-users::users.table.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('filament-max-users::users.table.description'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('username')
                    ->label(__('filament-max-users::users.table.username'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label(__('filament-max-users::users.table.phone'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->icon(fn (Model $record): ?string => UserPresenter::phoneIcon($record))
                    ->tooltip(UserPresenter::phoneTooltip()),
                TextColumn::make('phone_verified_at')
                    ->label(__('filament-max-users::users.table.phone_verified_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder(__('filament-max-users::users.view.phone_unverified'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('filament-max-users::users.table.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chat_links_count')
                    ->label(__('filament-max-users::users.table.chats_count'))
                    ->counts('chatLinks')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_activity_time')
                    ->label(__('filament-max-users::users.table.last_activity_time'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('profile_checked_at')
                    ->label(__('filament-max-users::users.table.profile_checked_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder(__('filament-max-users::users.view.never_checked'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_bot')
                    ->label(__('filament-max-users::users.table.filter_is_bot'))
                    ->options([
                        false => __('filament-max-users::users.table.filter_is_bot_user'),
                        true => __('filament-max-users::users.table.filter_is_bot_bot'),
                    ]),
                Filter::make('has_phone')
                    ->label(__('filament-max-users::users.table.filter_has_phone'))
                    ->query(static fn (Builder $query): Builder => $query->whereNotNull('phone')->where('phone', '!=', '')),
            ])
            ->defaultSort('user_id', 'desc');
    }
}
