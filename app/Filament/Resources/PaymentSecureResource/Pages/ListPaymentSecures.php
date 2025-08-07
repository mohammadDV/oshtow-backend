<?php

namespace App\Filament\Resources\PaymentSecureResource\Pages;

use App\Filament\Resources\PaymentSecureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentSecures extends ListRecords
{
    protected static string $resource = PaymentSecureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action removed - payment secures are created automatically by the system
        ];
    }
}
