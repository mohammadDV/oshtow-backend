<?php

namespace App\Filament\Resources\PaymentSecureResource\Pages;

use App\Filament\Resources\PaymentSecureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentSecure extends ViewRecord
{
    protected static string $resource = PaymentSecureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit action removed - admins cannot edit payment secures
        ];
    }
}
