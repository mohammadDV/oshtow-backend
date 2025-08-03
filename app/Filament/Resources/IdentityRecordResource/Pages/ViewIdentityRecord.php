<?php

namespace App\Filament\Resources\IdentityRecordResource\Pages;

use App\Filament\Resources\IdentityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIdentityRecord extends ViewRecord
{
    protected static string $resource = IdentityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(__('site.edit_identity_record')),
        ];
    }

    public function getTitle(): string
    {
        return __('site.View Identity Record');
    }
}
