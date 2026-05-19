<?php

namespace App\Filament\Widgets;

use App\Models\Animal;
use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenueThisWeek = (float) Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total');

        $newOrdersToday = Order::query()->whereDate('placed_at', today())->count();

        $pendingOrders = Order::query()
            ->whereIn('order_status', ['paid', 'processing'])
            ->count();

        $lowStockVariants = ProductVariant::query()->where('stock', '<', 5)->count();
        $lowStockAnimals = Animal::query()
            ->where('listing_type', 'pool')
            ->where('stock', '<', 5)
            ->count();
        $lowStock = $lowStockVariants + $lowStockAnimals;

        return [
            Stat::make('Revenue this week', '₦'.number_format($revenueThisWeek, 0))
                ->description('Paid orders, Mon–Sun')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('New orders today', $newOrdersToday)
                ->description('Placed since midnight')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('info'),

            Stat::make('Pending orders', $pendingOrders)
                ->description('Paid or processing — needs action')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),

            Stat::make('Low stock', $lowStock)
                ->description('Variants & pool animals below 5')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'gray'),
        ];
    }
}
