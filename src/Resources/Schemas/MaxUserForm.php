<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Schemas;

use DateTimeInterface;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Support\UserPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MaxUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::contextSection(),
                Section::make(__('filament-max-users::users.form.profile'))
                    ->columns(2)
                    ->schema([
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
                        Toggle::make('reset_phone_verification')
                            ->label(__('filament-max-users::users.form.reset_phone_verification'))
                            ->helperText(__('filament-max-users::users.form.reset_phone_verification_hint'))
                            ->visible(static fn (Model $record): bool => $record->getAttribute('phone_verified_at') !== null),
                        TextInput::make('email')
                            ->label(__('filament-max-users::users.form.email'))
                            ->email()
                            ->maxLength(256),
                        Toggle::make('is_bot')
                            ->label(__('filament-max-users::users.form.is_bot')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Служебные поля реестра: их заполняет синхронизация с MAX, поэтому на
     * странице редактирования они показываются, но не изменяются.
     */
    private static function contextSection(): Section
    {
        return Section::make(__('filament-max-users::users.form.context'))
            ->columns(2)
            ->schema([
                Image::make(
                    static fn (Model $record): string => self::avatarUrl($record),
                    static fn (Model $record): string => self::avatarAlt($record),
                )
                    ->imageSize(64)
                    ->visible(static fn (Model $record): bool => self::avatarUrl($record) !== ''),
                TextInput::make('user_id')
                    ->label(__('filament-max-users::users.view.user_id'))
                    ->disabled()
                    ->dehydrated(false),
                self::dateTimeField(
                    'phone_verified_at',
                    __('filament-max-users::users.view.phone_verified_at'),
                    __('filament-max-users::users.view.phone_unverified'),
                ),
                TextInput::make('last_activity_time')
                    ->label(__('filament-max-users::users.view.last_activity_time'))
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(static function (TextInput $component, Model $record): void {
                        $component->state(
                            self::formatDateTime($component, UserPresenter::lastActivityAt($record)),
                        );
                    }),
                self::dateTimeField(
                    'profile_checked_at',
                    __('filament-max-users::users.view.profile_checked_at'),
                    __('filament-max-users::users.view.never_checked'),
                ),
            ])
            ->columnSpanFull();
    }

    private static function dateTimeField(string $name, string $label, string $placeholder): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->disabled()
            ->dehydrated(false)
            ->placeholder($placeholder)
            ->formatStateUsing(
                static fn (TextInput $component, mixed $state): ?string => self::formatDateTime($component, $state),
            );
    }

    private static function formatDateTime(TextInput $component, mixed $state): ?string
    {
        $date = match (true) {
            $state instanceof DateTimeInterface => Carbon::parse($state),
            \is_string($state) && $state !== '' => Carbon::parse($state),
            default => null,
        };

        return $date?->translatedFormat(
            $component->getContainer()->getDefaultDateTimeDisplayFormat(),
        );
    }

    private static function avatarUrl(Model $record): string
    {
        $url = $record->getAttribute('avatar_url');

        return \is_string($url) ? $url : '';
    }

    private static function avatarAlt(Model $record): string
    {
        $name = $record->getAttribute('first_name');
        $lastName = $record->getAttribute('last_name');

        return \trim(\implode(' ', \array_filter(
            [$name, $lastName],
            static fn (mixed $part): bool => \is_string($part) && $part !== '',
        )));
    }
}
