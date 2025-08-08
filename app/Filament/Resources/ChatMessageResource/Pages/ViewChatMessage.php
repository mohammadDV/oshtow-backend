<?php

namespace App\Filament\Resources\ChatMessageResource\Pages;

use App\Filament\Resources\ChatMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewChatMessage extends ViewRecord
{
    protected static string $resource = ChatMessageResource::class;

    public function getTitle(): string
    {
        return __('site.view_chat_message');
    }
}
