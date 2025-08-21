<?php

namespace App\Filament\Resources\ManualTransactionResource\Pages;

use App\Filament\Resources\ManualTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManualTransactions extends ListRecords
{
    protected static string $resource = ManualTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - manual transactions are created through the API
        ];
    }
}
