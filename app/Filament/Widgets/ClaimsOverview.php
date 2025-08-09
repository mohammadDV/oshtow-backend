<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClaimResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TicketResource;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Carbon\Carbon;
use Domain\Ticket\Models\Ticket;
use Domain\Wallet\Models\WithdrawalTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClaimsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get date range for last month
        $lastMonth = Carbon::now()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        // Count projects and claims from last month
        $projectsLastMonth = Project::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $claimsLastMonth = Claim::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        // Total counts for comparison
        // $totalProjects = Project::count();
        // $totalClaims = Claim::count();

        return [
            Stat::make(__('site.projects_last_month'), $projectsLastMonth)
                ->description(__('site.projects_created_last_month'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Sample chart data
                ->url(ProjectResource::getUrl('index')),

            Stat::make(__('site.claims_last_month'), $claimsLastMonth)
                ->description(__('site.claims_created_last_month'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([3, 8, 5, 12, 6, 9, 4]) // Sample chart data
                ->url(ClaimResource::getUrl('index')),

            Stat::make(__('site.active_tickets'), Ticket::where('status', 'active')->count())
                ->description(__('site.tickets_need_attention'))
                ->descriptionIcon('heroicon-m-clock')
                ->chart([3, 8, 5, 12, 6, 9, 4]) // Sample chart data
                ->url(TicketResource::getUrl('index'))
                ->color('warning'),

            Stat::make(__('site.pending_withdrawal_transactions'), WithdrawalTransaction::where('status', WithdrawalTransaction::PENDING)->count())
                ->description(__('site.withdrawal_requests_awaiting_approval'))
                ->descriptionIcon('heroicon-m-clock')
                ->url(route('filament.admin.resources.withdrawal-transactions.index', ['tableFilters[status][value]' => WithdrawalTransaction::PENDING]))
                ->chart([3, 8, 5, 12, 6, 9, 4]) // Sample chart data
                ->color('danger'),
        ];
    }
}