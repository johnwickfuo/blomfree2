<?php

namespace App\Filament\Widgets;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $activeAffiliates = Affiliate::query()->where('status', 'active')->count();
        $pendingWithdrawals = AffiliateWithdrawal::query()->where('status', 'pending')->count();
        $commissionsThisMonth = (float) AffiliateCommission::query()
            ->whereIn('status', ['available', 'withdrawn'])
            ->whereBetween('earned_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        $pendingCommissions = (float) AffiliateCommission::query()
            ->where('status', 'pending')
            ->sum('amount');

        return [
            Stat::make('Active affiliates', $activeAffiliates)
                ->description('Codes currently accepted at checkout')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Pending withdrawals', $pendingWithdrawals)
                ->description('Awaiting your approval')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($pendingWithdrawals > 0 ? 'warning' : 'gray'),

            Stat::make('Commissions this month', '₦'.number_format($commissionsThisMonth, 0))
                ->description('Earned since the 1st')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info'),

            Stat::make('Pending payout liability', '₦'.number_format($pendingCommissions, 0))
                ->description('Pending balances awaiting release')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),
        ];
    }
}
