<?php

namespace App\Filament\Widgets;

use App\Models\Affiliate;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopAffiliatesWidget extends TableWidget
{
    protected static ?string $heading = 'Top 5 Affiliate Earners';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Affiliate::query()
                    ->where('status', 'active')
                    ->orderByDesc('total_earned')
                    ->limit(5),
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->description(fn (Affiliate $r): ?string => $r->user?->email),
                Tables\Columns\TextColumn::make('total_earned')->money('NGN')->label('Lifetime earned'),
                Tables\Columns\TextColumn::make('available_balance')->money('NGN')->label('Available'),
                Tables\Columns\TextColumn::make('total_withdrawn')->money('NGN')->label('Withdrawn'),
            ])
            ->paginated(false);
    }
}
