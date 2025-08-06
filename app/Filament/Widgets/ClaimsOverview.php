<?php

namespace App\Filament\Widgets;

use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClaimsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('site.total_projects'), Project::count())
                ->description(__('site.all_projects_in_system'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make(__('site.projects_with_claims'), Project::has('claims')->count())
                ->description(__('site.projects_that_have_claims'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make(__('site.total_claims'), Claim::count())
                ->description(__('site.all_claims_in_system'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make(__('site.pending_claims'), Claim::where('status', Claim::PENDING)->count())
                ->description(__('site.claims_awaiting_approval'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
