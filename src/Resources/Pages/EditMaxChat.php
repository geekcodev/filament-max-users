<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use GeekCo\FilamentMaxUsers\Resources\Actions\DeleteMaxChatAction;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

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

    protected function getRedirectUrl(): string
    {
        return MaxChatResource::getUrl('index');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->icon('heroicon-o-check')
            ->outlined();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->icon('heroicon-o-x-mark')
            ->outlined();
    }

    /**
     * У диалога нет own-метаданных для формы: сохранять нечего, оставляем
     * только контекст и действия в шапке.
     */
    protected function getFormActions(): array
    {
        return ChatPresenter::isDialog($this->getRecord()) ? [] : parent::getFormActions();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->openUserAction(),
            DeleteMaxChatAction::make()
                ->outlined(),
        ];
    }

    /**
     * У диалога метаданных чата нет: имя берётся из профиля собеседника.
     * Действие ведёт в карточку пользователя, где это имя можно изменить.
     */
    private function openUserAction(): Action
    {
        return Action::make('openUser')
            ->label(__('filament-max-users::chats.actions.open_user'))
            ->icon('heroicon-o-user')
            ->outlined()
            ->visible(function (): bool {
                $user = $this->dialogUser();

                return $user !== null && (Gate::allows('edit', $user) || Gate::allows('view', $user));
            })
            ->url(function (): ?string {
                $user = $this->dialogUser();

                if ($user === null) {
                    return null;
                }

                return MaxUserResource::getUrl(
                    Gate::allows('edit', $user) ? 'edit' : 'view',
                    ['record' => $user],
                );
            });
    }

    private function dialogUser(): ?Model
    {
        $chat = $this->getRecord();

        if (! $chat instanceof MaxChat || ! ChatPresenter::isDialog($chat)) {
            return null;
        }

        return $chat->chatUsers()->first()?->maxUser;
    }
}
