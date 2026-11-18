<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MaxChatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('filament-max-users::chats.form.title'))
                    ->maxLength(256),
                TextInput::make('link')
                    ->label(__('filament-max-users::chats.form.link'))
                    ->maxLength(512)
                    ->rules(['nullable', 'url:http,https']),
                TextInput::make('icon_url')
                    ->label(__('filament-max-users::chats.form.icon_url'))
                    ->maxLength(512)
                    ->rules(['nullable', 'url:http,https']),
                Textarea::make('description')
                    ->label(__('filament-max-users::chats.form.description'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
