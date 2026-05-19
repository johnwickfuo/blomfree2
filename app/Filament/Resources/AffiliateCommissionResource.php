<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateCommissionResource\Pages;
use App\Models\AffiliateCommission;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class AffiliateCommissionResource extends Resource
{
    protected static ?string $model = AffiliateCommission::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Affiliates';

    protected static ?string $navigationLabel = 'Commissions';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('earned_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.code')
                    ->label('Affiliate')
                    ->searchable(['affiliates.code']),
                Tables\Columns\TextColumn::make('order.reference')
                    ->label('Order')
                    ->searchable(['orders.reference']),
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
                Tables\Columns\TextColumn::make('earned_at')->since()->sortable(),
                Tables\Columns\TextColumn::make('available_at')->since()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'available' => 'Available',
                    'reversed' => 'Reversed',
                    'withdrawn' => 'Withdrawn',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('reverse')
                    ->label('Reverse')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (AffiliateCommission $r): bool => in_array($r->status, ['pending', 'available'], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')->required()->rows(3),
                    ])
                    ->action(function (AffiliateCommission $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $wasPending = $record->status === 'pending';
                            $amount = (float) $record->amount;
                            $affiliate = $record->affiliate()->lockForUpdate()->first();

                            $record->update([
                                'status' => 'reversed',
                                'reversed_at' => now(),
                                'reversal_reason' => $data['reason'],
                            ]);

                            if ($wasPending) {
                                $affiliate->update([
                                    'pending_balance' => (string) ((float) $affiliate->pending_balance - $amount),
                                    'total_earned' => (string) ((float) $affiliate->total_earned - $amount),
                                ]);
                            } else {
                                $affiliate->update([
                                    'available_balance' => (string) ((float) $affiliate->available_balance - $amount),
                                    'total_earned' => (string) ((float) $affiliate->total_earned - $amount),
                                ]);
                            }
                        });
                        Notification::make()->title('Commission reversed')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateCommissions::route('/'),
        ];
    }
}
