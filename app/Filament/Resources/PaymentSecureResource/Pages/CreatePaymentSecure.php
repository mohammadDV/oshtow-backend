<?php

namespace App\Filament\Resources\PaymentSecureResource\Pages;

use App\Filament\Resources\PaymentSecureResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentSecure extends CreateRecord
{
    protected static string $resource = PaymentSecureResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
