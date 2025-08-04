<?php

namespace App\Filament\Resources\ProjectCategoryResource\Pages;

use App\Filament\Resources\ProjectCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditProjectCategory extends EditRecord
{
    protected static string $resource = ProjectCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label(__('site.view_project_category')),
            Actions\DeleteAction::make()
                ->label(__('site.Delete'))
                ->before(function () {
                    Notification::make()
                        ->title(__('site.confirm_delete_project_category'))
                        ->body(__('site.confirm_delete_project_category_description'))
                        ->warning()
                        ->send();
                })
                ->after(function () {
                    Notification::make()
                        ->title(__('site.project_category_deleted_successfully'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure user_id cannot be changed - keep the original user_id
        $data['user_id'] = $this->getRecord()->user_id;

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title(__('site.project_category_updated_successfully'))
            ->success()
            ->send();
    }
}
