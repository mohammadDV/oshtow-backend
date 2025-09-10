<?php

namespace App\Filament\Widgets;

use Domain\Payment\Models\Transaction;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = null;

    public function getHeading(): string
    {
        return __('site.monthly_revenue');
    }

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        return __('site.revenue_breakdown_by_month_for_current_year');
    }

    protected function getData(): array
    {
        $currentYear = now()->year;
        $revenueData = Transaction::getRevenueByMonth($currentYear);

        $months = [];
        $revenues = [];

        $englishMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = $englishMonths[$month];
            $revenues[] = $revenueData[$month];
        }

        return [
            'datasets' => [
                [
                    'label' => __('site.revenue_irr'),
                    'data' => $revenues,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(245, 158, 11, 0.5)',
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(139, 92, 246, 0.5)',
                        'rgba(236, 72, 153, 0.5)',
                        'rgba(6, 182, 212, 0.5)',
                        'rgba(34, 197, 94, 0.5)',
                        'rgba(251, 146, 60, 0.5)',
                        'rgba(220, 38, 38, 0.5)',
                        'rgba(168, 85, 247, 0.5)',
                        'rgba(219, 39, 119, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(6, 182, 212)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(220, 38, 38)',
                        'rgb(168, 85, 247)',
                        'rgb(219, 39, 119)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return new Intl.NumberFormat("fa-IR").format(value) + " ریال"; }',
                    ],
                ],
            ],
        ];
    }
}