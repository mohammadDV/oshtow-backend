<?php

namespace App\Filament\Widgets;

use Domain\Ticket\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'تیکت‌ها بر اساس وضعیت';

    protected function getData(): array
    {
        $activeTickets = Ticket::where('status', 'active')->count();
        $closedTickets = Ticket::where('status', 'closed')->count();

        return [
            'datasets' => [
                [
                    'label' => __('site.tickets'),
                    'data' => [$activeTickets, $closedTickets],
                    'backgroundColor' => ['#f59e0b', '#10b981'],
                ],
            ],
            'labels' => [__('site.active'), __('site.closed')],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}