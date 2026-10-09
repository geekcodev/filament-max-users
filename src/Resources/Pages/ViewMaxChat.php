<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Services\MaxChatProfileService;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Database\Eloquent\Model;

class ViewMaxChat extends ViewRecord
{
    protected static string $resource = MaxChatResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('chat_id')
                    ->label(__('filament-max-users::chats.view.chat_id')),
                ImageEntry::make('icon_url')
                    ->label(__('filament-max-users::chats.view.icon_url'))
                    ->circular(),
                TextEntry::make('chat_type')
                    ->label(__('filament-max-users::chats.view.chat_type'))
                    ->badge()
                    ->color(static fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => 'info',
                        ChatType::Chat => 'success',
                        ChatType::Channel => 'warning',
                        null => 'gray',
                    })
                    ->formatStateUsing(static fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => __('filament-max-users::chats.chat_type.dialog'),
                        ChatType::Chat => __('filament-max-users::chats.chat_type.group'),
                        ChatType::Channel => __('filament-max-users::chats.chat_type.channel'),
                        null => __('filament-max-users::chats.chat_type.unknown'),
                    }),
                TextEntry::make('status')
                    ->label(__('filament-max-users::chats.view.status'))
                    ->badge()
                    ->color(static fn (MaxChatStatus $state): string => match ($state) {
                        MaxChatStatus::Active => 'success',
                        MaxChatStatus::Stopped => 'warning',
                        MaxChatStatus::Removed => 'danger',
                    })
                    ->formatStateUsing(static fn (MaxChatStatus $state): string => $state->label()),
                TextEntry::make('displayName')
                    ->label(__('filament-max-users::chats.view.display_name'))
                    ->getStateUsing(static fn (Model $record): string => ChatPresenter::displayName($record)),
                TextEntry::make('description')
                    ->label(__('filament-max-users::chats.view.description'))
                    ->placeholder(__('filament-max-users::chats.title.unknown'))
                    ->columnSpanFull(),
                TextEntry::make('link')
                    ->label(__('filament-max-users::chats.view.link'))
                    ->url(static fn (?string $state): ?string => ChatPresenter::safeUrl($state))
                    ->openUrlInNewTab()
                    ->placeholder(__('filament-max-users::chats.title.unknown')),
                TextEntry::make('last_activity_at')
                    ->label(__('filament-max-users::chats.view.last_activity_at'))
                    ->dateTime('d.m.Y H:i:s'),
                TextEntry::make('chat_checked_at')
                    ->label(__('filament-max-users::chats.view.chat_checked_at'))
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder(__('filament-max-users::chats.title.never_checked')),
                RepeatableEntry::make('chatUsers')
                    ->label(__('filament-max-users::chats.view.users'))
                    ->schema([
                        TextEntry::make('maxUser.first_name')
                            ->label(__('filament-max-users::chats.view.user_first_name')),
                        TextEntry::make('maxUser.last_name')
                            ->label(__('filament-max-users::chats.view.user_last_name')),
                        TextEntry::make('user_id')
                            ->label(__('filament-max-users::chats.view.user_id')),
                        TextEntry::make('status')
                            ->label(__('filament-max-users::chats.view.user_status'))
                            ->badge()
                            ->color(static fn (MaxChatStatus $state): string => match ($state) {
                                MaxChatStatus::Active => 'success',
                                MaxChatStatus::Stopped => 'warning',
                                MaxChatStatus::Removed => 'danger',
                            })
                            ->formatStateUsing(static fn (MaxChatStatus $state): string => $state->label()),
                        TextEntry::make('last_activity_at')
                            ->label(__('filament-max-users::chats.view.user_last_activity_at'))
                            ->dateTime('d.m.Y H:i:s'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->refreshMetadataAction(),
            $this->deleteAction(),
        ];
    }

    /**
     * Забрать метаданные чата из MAX: название, описание, ссылку и иконку.
     *
     * Один вызов sync(), а не refresh(): администратор нажал на конкретный чат,
     * а refresh() пропускает чаты с свежей отметкой chat_checked_at.
     */
    private function refreshMetadataAction(): Action
    {
        return Action::make('refreshChat')
            ->label(__('filament-max-users::chats.actions.refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading(__('filament-max-users::chats.actions.refresh_heading'))
            ->modalDescription(__('filament-max-users::chats.actions.refresh_description'))
            ->modalSubmitActionLabel(__('filament-max-users::chats.actions.refresh_submit'))
            ->authorize(config()->string('filament-max-users.permissions.chats.manage', 'chats.manage'))
            ->action(function (): void {
                /** @var MaxChat $record */
                $record = $this->getRecord();

                $updated = app(MaxChatProfileService::class)->sync($record->chat_id);

                if ($updated) {
                    $record->refresh();

                    Notification::make()
                        ->title(__('filament-max-users::chats.actions.refresh_success'))
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title(__('filament-max-users::chats.actions.refresh_failure'))
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Удалить запись реестра вместе со строками связи max_chat_users: внешних
     * ключей между таблицами нет, поэтому связи удаляются явно, иначе они остались
     * бы висеть без чата.
     */
    private function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('filament-max-users::chats.actions.delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament-max-users::chats.actions.delete_heading'))
            ->modalDescription(__('filament-max-users::chats.actions.delete_description'))
            ->modalSubmitActionLabel(__('filament-max-users::chats.actions.delete_submit'))
            ->authorize(config()->string('filament-max-users.permissions.chats.delete', 'chats.delete'))
            ->action(function (): void {
                /** @var MaxChat $record */
                $record = $this->getRecord();

                $record->chatUsers()->delete();
                $record->delete();

                Notification::make()
                    ->title(__('filament-max-users::chats.actions.delete_success'))
                    ->success()
                    ->send();

                $this->redirect(MaxChatResource::getUrl('index'));
            });
    }
}
