<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use GeekCo\FilamentMaxUsers\Resources\Actions\DeleteMaxChatAction;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;

class EditMaxChat extends EditRecord
{
    protected static string $resource = MaxChatResource::class;

    public function getTitle(): string
    {
        return __('filament-max-users::chats.pages.edit_title');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-max-users::chats.actions.edit_success');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteMaxChatAction::make(),
        ];
    }
}
