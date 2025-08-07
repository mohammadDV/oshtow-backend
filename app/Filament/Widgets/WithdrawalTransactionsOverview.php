<?php

namespace App\Filament\Widgets;

use Domain\Wallet\Models\WithdrawalTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WithdrawalTransactionsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('site.pending_withdrawal_transactions'), WithdrawalTransaction::where('status', WithdrawalTransaction::PENDING)->count())
                ->description(__('site.withdrawal_requests_awaiting_approval'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.withdrawal-transactions.index', ['tableFilters[status][value]' => WithdrawalTransaction::PENDING])),

            Stat::make(__('site.completed_withdrawal_transactions'), WithdrawalTransaction::where('status', WithdrawalTransaction::COMPLETED)->count())
                ->description(__('site.approved_withdrawal_requests'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(route('filament.admin.resources.withdrawal-transactions.index', ['tableFilters[status][value]' => WithdrawalTransaction::COMPLETED])),

            Stat::make(__('site.rejected_withdrawal_transactions'), WithdrawalTransaction::where('status', WithdrawalTransaction::REJECT)->count())
                ->description(__('site.rejected_withdrawal_requests'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.withdrawal-transactions.index', ['tableFilters[status][value]' => WithdrawalTransaction::REJECT])),

            Stat::make(__('site.total_withdrawal_transactions'), WithdrawalTransaction::count())
                ->description(__('site.all_withdrawal_requests'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary')
                ->url(route('filament.admin.resources.withdrawal-transactions.index')),
        ];
    }
}
