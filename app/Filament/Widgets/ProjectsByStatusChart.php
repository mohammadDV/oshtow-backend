<?php

namespace App\Filament\Widgets;

use Domain\Project\Models\Project;
use Filament\Widgets\ChartWidget;

class ProjectsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = null;

    protected function getData(): array
    {
        $pendingProjects = Project::where('status', Project::PENDING)->count();
        $approvedProjects = Project::where('status', Project::APPROVED)->count();
        $inProgressProjects = Project::where('status', Project::INPROGRESS)->count();
        $completedProjects = Project::where('status', Project::COMPLETED)->count();
        $cancelledProjects = Project::where('status', Project::CANCELLED)->count();

        return [
            'datasets' => [
                [
                    'label' => __('site.projects'),
                    'data' => [
                        $pendingProjects,
                        $approvedProjects,
                        $inProgressProjects,
                        $completedProjects,
                        $cancelledProjects
                    ],
                    'backgroundColor' => [
                        '#f59e0b', // Orange for pending
                        '#3b82f6', // Blue for approved
                        '#8b5cf6', // Purple for in progress
                        '#10b981', // Green for completed
                        '#ef4444', // Red for cancelled
                    ],
                ],
            ],
            'labels' => [
                __('site.pending'),
                __('site.approved'),
                __('site.in_progress'),
                __('site.completed'),
                __('site.cancelled')
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return __('site.projects_by_status');
    }
}
