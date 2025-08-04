<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title(__('site.project_created_successfully'))
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle categories relationship
        if (isset($data['categories'])) {
            $categories = $data['categories'];
            unset($data['categories']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Sync categories if they were provided
        if (isset($this->data['categories'])) {
            $record->categories()->sync($this->data['categories']);
        }
    }
}
