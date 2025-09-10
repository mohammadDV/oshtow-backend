<?php

namespace App\Filament\Widgets;

use Domain\Payment\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Total revenue for current year (only completed transactions)
        $yearlyRevenue = Transaction::whereYear('created_at', $currentYear)
            ->where('status', Transaction::COMPLETED)
            ->get()
            ->sum('revenue');

        // Total revenue for current month (only completed transactions)
        $monthlyRevenue = Transaction::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('status', Transaction::COMPLETED)
            ->get()
            ->sum('revenue');

        // Revenue by transaction type for current year (only completed transactions)
        $revenueByType = Transaction::getRevenueByType();
        $walletRevenue = $revenueByType[Transaction::WALLET];
        $planRevenue = $revenueByType[Transaction::PLAN];
        $identityRevenue = $revenueByType[Transaction::IDENTITY];
        $secureRevenue = $revenueByType[Transaction::SECURE];

        return [
            Stat::make(__('site.total_revenue') . ' (' . $currentYear . ')', number_format($yearlyRevenue) . ' تومان')
                ->description(__('site.revenue_for') . ' ' . $currentYear)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('site.monthly_revenue'), number_format($monthlyRevenue) . ' تومان')
                ->description(__('site.revenue_for_current_month'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make(__('site.wallet_revenue'), number_format($walletRevenue) . ' تومان')
                ->description(__('site.wallet_transactions_revenue'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('gray'),

            Stat::make(__('site.plan_revenue'), number_format($planRevenue) . ' تومان')
                ->description(__('site.plan_transactions_revenue'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),

            Stat::make(__('site.identity_revenue'), number_format($identityRevenue) . ' تومان')
                ->description(__('site.identity_transactions_revenue'))
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make(__('site.secure_revenue'), number_format($secureRevenue) . ' تومان')
                ->description(__('site.secure_transactions_revenue'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
        ];
    }
}
