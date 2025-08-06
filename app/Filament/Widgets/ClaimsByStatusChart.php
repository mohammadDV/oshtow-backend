<?php

namespace App\Filament\Widgets;

use Domain\Claim\Models\Claim;
use Filament\Widgets\ChartWidget;

class ClaimsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('site.claims_by_status');
    }

    protected function getData(): array
    {
        $statuses = [
            Claim::PENDING => __('site.pending'),
            Claim::APPROVED => __('site.approved'),
            Claim::PAID => __('site.paid'),
            Claim::INPROGRESS => __('site.in_progress'),
            Claim::DELIVERED => __('site.delivered'),
            Claim::CANCELED => __('site.canceled'),
        ];

        $data = [];
        $labels = [];
        $colors = [
            '#f59e0b', // warning - pending
            '#10b981', // success - approved
            '#3b82f6', // primary - paid
            '#06b6d4', // info - in_progress
            '#10b981', // success - delivered
            '#ef4444', // danger - canceled
        ];

        foreach ($statuses as $status => $label) {
            $count = Claim::where('status', $status)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $label;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('site.claims'),
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
