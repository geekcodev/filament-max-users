<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers;

use Filament\Contracts\Plugin;
use Filament\Panel;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;

class FilamentMaxUsersPlugin implements Plugin
{
    /** @var class-string */
    protected string $userResource = MaxUserResource::class;

    /** @var class-string */
    protected string $chatResource = MaxChatResource::class;

    public static function make(): static
    {
        /** @var static */
        return app(static::class);
    }

    /**
     * @param  class-string  $resource
     */
    public function userResource(string $resource): static
    {
        $this->userResource = $resource;

        return $this;
    }

    /**
     * @param  class-string  $resource
     */
    public function chatResource(string $resource): static
    {
        $this->chatResource = $resource;

        return $this;
    }

    public function getId(): string
    {
        return 'filament-max-users';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            $this->userResource,
            $this->chatResource,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
