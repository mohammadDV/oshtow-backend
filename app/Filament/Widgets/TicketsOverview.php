<?php

namespace App\Filament\Widgets;

use Domain\Ticket\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TicketsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('site.total_tickets'), Ticket::count())
                ->description(__('site.all_tickets_in_system'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make(__('site.active_tickets'), Ticket::where('status', 'active')->count())
                ->description(__('site.tickets_need_attention'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('site.closed_tickets'), Ticket::where('status', 'closed')->count())
                ->description(__('site.resolved_tickets'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('site.unread_messages'), \Domain\Ticket\Models\TicketMessage::where('status', 'pending')->where('user_id', '!=', Auth::user()->id)->count())
                ->description(__('site.messages_waiting_to_be_read'))
                ->descriptionIcon('heroicon-m-envelope')
                ->color('danger'),
        ];
    }
}