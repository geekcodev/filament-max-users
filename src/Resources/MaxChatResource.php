<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources;

use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats;
use GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat;
use GeekCo\FilamentMaxUsers\Resources\Tables\MaxChatsTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class MaxChatResource extends Resource
{
    public static function getModel(): string
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> */
        return config('filament-max-users.chats_model', \GeekCo\LaravelMaxClient\Models\MaxChat::class);
    }

    public static function getModelLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.chats.label');

        return $label ?? __('filament-max-users::chats.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.chats.plural_label');

        return $label ?? __('filament-max-users::chats.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.chats.navigation_label');

        return $label ?? __('filament-max-users::chats.resource.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        /** @var string|null $group */
        $group = config('filament-max-users.ui.navigation_group');

        return $group;
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        /** @var string|BackedEnum|Htmlable|null $icon */
        $icon = config('filament-max-users.ui.chats.navigation_icon');

        return $icon ?? 'heroicon-o-chat-bubble-left-right';
    }

    public static function getNavigationSort(): ?int
    {
        /** @var int|null $sort */
        $sort = config('filament-max-users.ui.chats.navigation_sort');

        return $sort;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        /** @var string|null $slug */
        $slug = config('filament-max-users.ui.chats.slug');

        return $slug ?? 'max-chats';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return MaxChatsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaxChats::route('/'),
            'view' => ViewMaxChat::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $permission = config()->string('filament-max-users.permissions.chats.view', 'chats.view');

        $user = auth()->user();

        return $user !== null && $user->can($permission);
    }

    public static function canView(Model $record): bool
    {
        return static::canAccess();
    }
}
