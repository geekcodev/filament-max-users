<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use GeekCo\FilamentMaxUsers\FilamentMaxUsersPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->default()
            ->plugin(FilamentMaxUsersPlugin::make());
    }
}
