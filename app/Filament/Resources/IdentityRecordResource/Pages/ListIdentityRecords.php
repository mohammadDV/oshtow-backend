<?php

namespace App\Filament\Resources\IdentityRecordResource\Pages;

use App\Filament\Resources\IdentityRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIdentityRecords extends ListRecords
{
    protected static string $resource = IdentityRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('site.create_identity_record')),
        ];
    }

    public function getTitle(): string
    {
        return __('site.Identity Records Management');
    }
}
