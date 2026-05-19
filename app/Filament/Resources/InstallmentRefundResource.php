<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentRefundResource\Pages;
use App\Models\InstallmentRefund;
use App\Services\InstallmentService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentRefundResource extends Resource
{
    protected static ?string $model = InstallmentRefund::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationGroup = 'Installments';

    protected static ?string $navigationLabel = 'Refunds';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('plan.reference')
                    ->label('Plan')
                    ->searchable(['installment_plans.reference']),
                Tables\Columns\TextColumn::make('plan.user.name')
                    ->label('Customer')
                    ->description(fn (InstallmentRefund $r): ?string => $r->plan?->user?->email),
                Tables\Columns\TextColumn::make('amount_paid_total')->money('NGN')->label('Paid total'),
                Tables\Columns\TextColumn::make('refund_amount')->money('NGN')->label('Refund')->sortable(),
                Tables\Columns\TextColumn::make('triggered_by')->badge()->formatStateUsing(fn (string $s) => str_replace('_', ' ', $s)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'paid' => 'success',
                        'pending', 'approved' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('requested_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (InstallmentRefund $r): bool => in_array($r->status, ['pending', 'approved'], true))
                    ->form([
                        Forms\Components\TextInput::make('payment_reference')->required()->label('Bank transfer reference'),
                        Forms\Components\Textarea::make('admin_notes')->rows(2),
                    ])
                    ->action(function (InstallmentRefund $record, array $data, InstallmentService $service): void {
                        $service->markRefundPaid($record, $data['payment_reference'], $data['admin_notes'] ?? null);
                        Notification::make()->title('Refund marked paid — customer notified.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (InstallmentRefund $r): bool => in_array($r->status, ['pending', 'approved'], true))
                    ->form([Forms\Components\Textarea::make('reason')->required()->rows(3)])
                    ->action(function (InstallmentRefund $record, array $data, InstallmentService $service): void {
                        $service->rejectRefund($record, $data['reason']);
                        Notification::make()->title('Refund rejected.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentRefunds::route('/'),
        ];
    }
}
