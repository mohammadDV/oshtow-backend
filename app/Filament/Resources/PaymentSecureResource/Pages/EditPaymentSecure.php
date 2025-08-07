<?php

namespace App\Filament\Resources\PaymentSecureResource\Pages;

use App\Filament\Resources\PaymentSecureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSecure extends EditRecord
{
    protected static string $resource = PaymentSecureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Delete action removed - admins cannot delete payment secures
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
