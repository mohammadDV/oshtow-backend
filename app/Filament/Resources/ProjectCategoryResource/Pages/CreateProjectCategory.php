<?php

namespace App\Filament\Resources\ProjectCategoryResource\Pages;

use App\Filament\Resources\ProjectCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateProjectCategory extends CreateRecord
{
    protected static string $resource = ProjectCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Check if the authenticated user is an admin (level 3)
        if (Auth::user()->level !== 3) {
            Notification::make()
                ->title(__('site.only_admin_users_allowed'))
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the user_id to the authenticated user
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title(__('site.project_category_created_successfully'))
            ->success()
            ->send();
    }
}
