<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use GeekCo\FilamentMaxUsers\Resources\MaxUserResource;

class ListMaxUsers extends ListRecords
{
    protected static string $resource = MaxUserResource::class;
}
