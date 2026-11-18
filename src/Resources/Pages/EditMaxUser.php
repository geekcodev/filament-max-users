<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use GeekCo\FilamentMaxUsers\Resources\Actions\DeleteMaxUserAction;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;

class EditMaxUser extends EditRecord
{
    protected static string $resource = MaxUserResource::class;

    public function getTitle(): string
    {
        return __('filament-max-users::users.pages.edit_title');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-max-users::users.actions.edit_success');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteMaxUserAction::make(),
        ];
    }
}
