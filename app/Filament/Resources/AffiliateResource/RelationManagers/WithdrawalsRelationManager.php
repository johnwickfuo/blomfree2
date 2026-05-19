<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WithdrawalsRelationManager extends RelationManager
{
    protected static string $relationship = 'withdrawals';

    protected static ?string $title = 'Withdrawals';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('net_amount')->money('NGN')->label('Net'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'paid' => 'success',
                        'pending', 'approved' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->since()->sortable(),
                Tables\Columns\TextColumn::make('processed_at')->dateTime()->since()->toggleable(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
