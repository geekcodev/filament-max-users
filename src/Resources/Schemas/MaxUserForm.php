<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MaxUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label(__('filament-max-users::users.form.first_name'))
                    ->required()
                    ->maxLength(128),
                TextInput::make('last_name')
                    ->label(__('filament-max-users::users.form.last_name'))
                    ->maxLength(128),
                TextInput::make('username')
                    ->label(__('filament-max-users::users.form.username'))
                    ->maxLength(128),
                TextInput::make('name')
                    ->label(__('filament-max-users::users.form.name'))
                    ->maxLength(256),
                Textarea::make('description')
                    ->label(__('filament-max-users::users.form.description'))
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->label(__('filament-max-users::users.form.phone'))
                    ->tel()
                    ->maxLength(32),
                TextInput::make('email')
                    ->label(__('filament-max-users::users.form.email'))
                    ->email()
                    ->maxLength(256),
                Toggle::make('is_bot')
                    ->label(__('filament-max-users::users.form.is_bot')),
            ]);
    }
}
