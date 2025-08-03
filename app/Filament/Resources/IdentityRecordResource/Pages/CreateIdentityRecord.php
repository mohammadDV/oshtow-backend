<?php

namespace App\Filament\Resources\IdentityRecordResource\Pages;

use App\Filament\Resources\IdentityRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIdentityRecord extends CreateRecord
{
    protected static string $resource = IdentityRecordResource::class;

    public function getTitle(): string
    {
        return __('site.Create Identity Record');
    }
}
