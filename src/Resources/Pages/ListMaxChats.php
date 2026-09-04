<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use GeekCo\FilamentMaxUsers\Resources\MaxChatResource;

class ListMaxChats extends ListRecords
{
    protected static string $resource = MaxChatResource::class;
}
