<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\MaxPhpClient\Enum\ChatType;

class ViewMaxChat extends ViewRecord
{
    protected static string $resource = MaxChatResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('id')
                    ->label(__('filament-max-users::chats.view.id')),
                TextEntry::make('chat_id')
                    ->label(__('filament-max-users::chats.view.chat_id')),
                TextEntry::make('chat_type')
                    ->label(__('filament-max-users::chats.view.chat_type'))
                    ->badge()
                    ->color(fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => 'info',
                        ChatType::Chat => 'success',
                        ChatType::Channel => 'warning',
                        null => 'gray',
                    })
                    ->formatStateUsing(fn (?ChatType $state): string => match ($state) {
                        ChatType::Dialog => __('filament-max-users::chats.chat_type.dialog'),
                        ChatType::Chat => __('filament-max-users::chats.chat_type.group'),
                        ChatType::Channel => __('filament-max-users::chats.chat_type.channel'),
                        null => __('filament-max-users::chats.chat_type.unknown'),
                    }),
                TextEntry::make('maxUser.first_name')
                    ->label(__('filament-max-users::chats.view.user_name')),
                TextEntry::make('user_id')
                    ->label(__('filament-max-users::chats.view.user_id')),
                TextEntry::make('status')
                    ->label(__('filament-max-users::chats.view.status'))
                    ->badge()
                    ->color(fn (MaxChatStatus $state): string => match ($state) {
                        MaxChatStatus::Active => 'success',
                        MaxChatStatus::Stopped => 'warning',
                        MaxChatStatus::Removed => 'danger',
                    })
                    ->formatStateUsing(fn (MaxChatStatus $state): string => $state->label()),
                TextEntry::make('last_activity_at')
                    ->label(__('filament-max-users::chats.view.last_activity_at'))
                    ->dateTime('d.m.Y H:i:s'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $deletePermission = config()->string('filament-max-users.permissions.chats.delete', 'chats.delete');

        return [
            Action::make('delete')
                ->label(__('filament-max-users::chats.actions.delete'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('filament-max-users::chats.actions.delete_heading'))
                ->modalDescription(__('filament-max-users::chats.actions.delete_description'))
                ->modalSubmitActionLabel(__('filament-max-users::chats.actions.delete_submit'))
                ->authorize($deletePermission)
                ->action(function (): void {
                    /** @var MaxChat $record */
                    $record = $this->getRecord();
                    $record->delete();

                    Notification::make()
                        ->title(__('filament-max-users::chats.actions.delete_success'))
                        ->success()
                        ->send();

                    $this->redirect(MaxChatResource::getUrl('index'));
                }),
        ];
    }
}
