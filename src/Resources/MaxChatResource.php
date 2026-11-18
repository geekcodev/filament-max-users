<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources;

use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use GeekCo\FilamentMaxUsers\Resources\Pages\EditMaxChat;
use GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxChats;
use GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxChat;
use GeekCo\FilamentMaxUsers\Resources\Schemas\MaxChatForm;
use GeekCo\FilamentMaxUsers\Resources\Tables\MaxChatsTable;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class MaxChatResource extends Resource
{
    public static function getModel(): string
    {
        /** @var class-string<Model> */
        return config()->string('laravel-max-client.chats.model', MaxChat::class);
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
        return MaxChatForm::configure($schema);
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
            'edit' => EditMaxChat::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return Gate::allows('edit', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', static::getModel());
    }

    public static function canView(Model $record): bool
    {
        return Gate::allows('view', $record);
    }
}
