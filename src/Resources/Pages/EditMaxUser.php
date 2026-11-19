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

    protected function getRedirectUrl(): string
    {
        return MaxUserResource::getUrl('index');
    }

    /**
     * Сброс отметки телефона: явный переключатель или изменение номера.
     * Служебное поле phone_verified_at в форме напрямую не редактируется,
     * но сохранение должно снимать устаревшее подтверждение.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $phoneChanged = \array_key_exists('phone', $data)
            && $data['phone'] !== $record->getAttribute('phone');

        if (($data['reset_phone_verification'] ?? false) || $phoneChanged) {
            $data['phone_verified_at'] = null;
        }

        unset($data['reset_phone_verification']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteMaxUserAction::make(),
        ];
    }
}
