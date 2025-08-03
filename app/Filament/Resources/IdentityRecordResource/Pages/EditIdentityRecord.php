<?php

namespace App\Filament\Resources\IdentityRecordResource\Pages;

use App\Filament\Resources\IdentityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIdentityRecord extends EditRecord
{
    protected static string $resource = IdentityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label(__('site.view_identity_record')),
        ];
    }

    public function getTitle(): string
    {
        return __('site.Edit Identity Record');
    }
}
