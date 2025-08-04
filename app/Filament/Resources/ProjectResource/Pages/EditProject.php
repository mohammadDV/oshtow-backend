<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label(__('site.view_project')),
            // Actions\DeleteAction::make()
            //     ->label(__('site.delete'))
            //     ->before(function () {
            //         Notification::make()
            //             ->title(__('site.confirm_delete_project'))
            //             ->body(__('site.confirm_delete_project_description'))
            //             ->warning()
            //             ->send();
            //     })
            //     ->after(function () {
            //         Notification::make()
            //             ->title(__('site.project_deleted_successfully'))
            //             ->success()
            //             ->send();
            //     }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Load categories for the form
        $data['categories'] = $record->categories->pluck('id')->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure user_id cannot be changed - keep the original user_id
        $data['user_id'] = $this->getRecord()->user_id;

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

        Notification::make()
            ->title(__('site.project_updated_successfully'))
            ->success()
            ->send();
    }
}
