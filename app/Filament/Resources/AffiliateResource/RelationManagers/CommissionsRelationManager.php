<?php

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissions';

    protected static ?string $title = 'Commissions';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('earned_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order.reference')->label('Order')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'available' => 'success',
                        'pending' => 'warning',
                        'reversed' => 'danger',
                        'withdrawn' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('earned_at')->dateTime()->since()->sortable(),
                Tables\Columns\TextColumn::make('available_at')->dateTime()->since()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'available' => 'Available',
                    'reversed' => 'Reversed',
                    'withdrawn' => 'Withdrawn',
                ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
