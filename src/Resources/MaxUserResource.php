<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources;

use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use GeekCo\FilamentMaxUsers\Resources\Pages\EditMaxUser;
use GeekCo\FilamentMaxUsers\Resources\Pages\ListMaxUsers;
use GeekCo\FilamentMaxUsers\Resources\Pages\ViewMaxUser;
use GeekCo\FilamentMaxUsers\Resources\Schemas\MaxUserForm;
use GeekCo\FilamentMaxUsers\Resources\Tables\MaxUsersTable;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class MaxUserResource extends Resource
{
    public static function getModel(): string
    {
        /** @var class-string<Model> */
        return config()->string('laravel-max-client.users.model', MaxUser::class);
    }

    public static function getModelLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.users.label');

        return $label ?? __('filament-max-users::users.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.users.plural_label');

        return $label ?? __('filament-max-users::users.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        /** @var string|null $label */
        $label = config('filament-max-users.ui.users.navigation_label');

        return $label ?? __('filament-max-users::users.resource.navigation_label');
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
        $icon = config('filament-max-users.ui.users.navigation_icon');

        return $icon ?? 'heroicon-o-users';
    }

    public static function getNavigationSort(): ?int
    {
        /** @var int|null $sort */
        $sort = config('filament-max-users.ui.users.navigation_sort');

        return $sort;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        /** @var string|null $slug */
        $slug = config('filament-max-users.ui.users.slug');

        return $slug ?? 'max-users';
    }

    public static function form(Schema $schema): Schema
    {
        return MaxUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaxUsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaxUsers::route('/'),
            'view' => ViewMaxUser::route('/{record}'),
            'edit' => EditMaxUser::route('/{record}/edit'),
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
