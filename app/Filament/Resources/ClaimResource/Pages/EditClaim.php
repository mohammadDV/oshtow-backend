<?php

namespace App\Filament\Resources\ClaimResource\Pages;

use App\Filament\Resources\ClaimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditClaim extends EditRecord
{
    protected static string $resource = ClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label(__('site.view_claim')),
            Actions\DeleteAction::make()
                ->label(__('site.delete_claim'))
                ->before(function () {
                    Notification::make()
                        ->title(__('site.confirm_delete_claim'))
                        ->body(__('site.confirm_delete_claim_description'))
                        ->warning()
                        ->send();
                })
                ->after(function () {
                    Notification::make()
                        ->title(__('site.claim_deleted_successfully'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title(__('site.claim_updated_successfully'))
            ->success()
            ->send();
    }
}
