<?php

namespace App\Filament\Resources\ManualTransactionResource\Pages;

use App\Filament\Resources\ManualTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManualTransaction extends EditRecord
{
    protected static string $resource = ManualTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action - transactions cannot be deleted
        ];
    }
}
