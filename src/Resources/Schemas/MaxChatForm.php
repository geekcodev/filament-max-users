<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;

class MaxChatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-max-users::chats.form.dialog_context'))
                    ->visible(static fn (Model $record): bool => ChatPresenter::isDialog($record))
                    ->columns(2)
                    ->schema([
                        TextInput::make('chat_id')
                            ->label(__('filament-max-users::chats.view.chat_id'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('chat_type')
                            ->label(__('filament-max-users::chats.view.chat_type'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(static fn (mixed $state): string => self::typeLabel($state)),
                        TextInput::make('status')
                            ->label(__('filament-max-users::chats.view.status'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(static fn (mixed $state): string => self::statusLabel($state)),
                        TextInput::make('display_name')
                            ->label(__('filament-max-users::chats.view.display_name'))
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(static function (TextInput $component, Model $record): void {
                                $component->state(ChatPresenter::displayName($record));
                            }),
                    ])
                    ->columnSpanFull(),
                Text::make(__('filament-max-users::chats.form.dialog_notice'))
                    ->visible(static fn (Model $record): bool => ChatPresenter::isDialog($record))
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->label(__('filament-max-users::chats.form.title'))
                    ->maxLength(256)
                    ->visible(static fn (Model $record): bool => ! ChatPresenter::isDialog($record)),
                TextInput::make('link')
                    ->label(__('filament-max-users::chats.form.link'))
                    ->maxLength(512)
                    ->rules(['nullable', 'url:http,https'])
                    ->visible(static fn (Model $record): bool => ! ChatPresenter::isDialog($record)),
                TextInput::make('icon_url')
                    ->label(__('filament-max-users::chats.form.icon_url'))
                    ->maxLength(512)
                    ->rules(['nullable', 'url:http,https'])
                    ->visible(static fn (Model $record): bool => ! ChatPresenter::isDialog($record)),
                Textarea::make('description')
                    ->label(__('filament-max-users::chats.form.description'))
                    ->rows(3)
                    ->visible(static fn (Model $record): bool => ! ChatPresenter::isDialog($record))
                    ->columnSpanFull(),
            ]);
    }

    private static function typeLabel(mixed $state): string
    {
        $type = $state instanceof ChatType
            ? $state
            : (\is_string($state) ? ChatType::tryFrom($state) : null);

        return match ($type) {
            ChatType::Dialog => __('filament-max-users::chats.chat_type.dialog'),
            ChatType::Chat => __('filament-max-users::chats.chat_type.group'),
            ChatType::Channel => __('filament-max-users::chats.chat_type.channel'),
            default => __('filament-max-users::chats.chat_type.unknown'),
        };
    }

    private static function statusLabel(mixed $state): string
    {
        $status = $state instanceof MaxChatStatus
            ? $state
            : (\is_string($state) ? MaxChatStatus::tryFrom($state) : null);

        return $status?->label() ?? (\is_string($state) ? $state : '');
    }
}
