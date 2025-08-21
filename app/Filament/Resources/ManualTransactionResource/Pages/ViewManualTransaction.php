<?php

namespace App\Filament\Resources\ManualTransactionResource\Pages;

use App\Filament\Resources\ManualTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewManualTransaction extends ViewRecord
{
    protected static string $resource = ManualTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
