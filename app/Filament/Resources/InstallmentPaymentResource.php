<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPaymentResource\Pages;
use App\Models\InstallmentPayment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentPaymentResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Installments';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('plan.reference')
                    ->label('Plan')
                    ->searchable(['installment_plans.reference']),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('payment_gateway')->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed', 'reversed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_down_payment')->boolean(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')->options(array_combine(InstallmentPayment::STATUSES, InstallmentPayment::STATUSES)),
                Tables\Filters\SelectFilter::make('payment_gateway')->options(array_combine(InstallmentPayment::GATEWAYS, InstallmentPayment::GATEWAYS)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentPayments::route('/'),
        ];
    }
}
