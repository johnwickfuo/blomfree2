<?php

namespace App\Filament\Widgets;

use App\Models\InstallmentPlan;
use App\Models\InstallmentRefund;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstallmentOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $pendingRequests = InstallmentPlan::query()->where('status', 'pending_approval')->count();
        $overdueSoon = InstallmentPlan::query()
            ->where('status', 'active')
            ->whereDate('deadline', '<=', now()->addDays(7))
            ->count();
        $defaultedThisMonth = InstallmentPlan::query()
            ->where('status', 'defaulted')
            ->whereBetween('defaulted_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $pendingRefunds = InstallmentRefund::query()->where('status', 'pending')->count();
        $activeValue = (float) InstallmentPlan::query()
            ->where('status', 'active')
            ->sum('total_amount');

        return [
            Stat::make('Pending requests', $pendingRequests)
                ->description('Land plans awaiting approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingRequests > 0 ? 'warning' : 'gray'),

            Stat::make('Deadline ≤ 7 days', $overdueSoon)
                ->description('Active plans nearing deadline')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdueSoon > 0 ? 'danger' : 'gray'),

            Stat::make('Defaulted this month', $defaultedThisMonth)
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('gray'),

            Stat::make('Pending refunds', $pendingRefunds)
                ->description('Awaiting your payout')
                ->descriptionIcon('heroicon-o-receipt-refund')
                ->color($pendingRefunds > 0 ? 'warning' : 'gray'),

            Stat::make('Active plan value', '₦'.number_format($activeValue, 0))
                ->description('Sum of total_amount across active plans')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),
        ];
    }
}
