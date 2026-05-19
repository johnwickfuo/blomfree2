<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateWithdrawalResource\Pages;
use App\Models\AffiliateWithdrawal;
use App\Services\AffiliateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateWithdrawalResource extends Resource
{
    protected static ?string $model = AffiliateWithdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Affiliates';

    protected static ?string $navigationLabel = 'Withdrawals';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('reference')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('affiliate.code')->label('Affiliate')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('status')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('amount')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('fee')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('net_amount')->prefix('₦')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Bank (snapshotted at request)')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('bank_snapshot.bank_name')->label('Bank')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('bank_snapshot.bank_account_number')->label('Account #')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('bank_snapshot.bank_account_name')->label('Account name')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Processing')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('payment_reference')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('processed_at')->disabled()->dehydrated(false),
                    Forms\Components\Textarea::make('admin_notes')->disabled()->dehydrated(false)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('affiliate.code')
                    ->label('Affiliate')
                    ->searchable(['affiliates.code'])
                    ->description(fn (AffiliateWithdrawal $r): ?string => $r->affiliate?->user?->email),
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
                Tables\Columns\TextColumn::make('requested_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AffiliateWithdrawal $r): bool => in_array($r->status, ['pending', 'approved'], true))
                    ->form([
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Bank transfer reference')
                            ->required()
                            ->helperText('The transfer ID/reference from your bank app.'),
                        Forms\Components\Textarea::make('admin_notes')->rows(2),
                    ])
                    ->action(function (AffiliateWithdrawal $record, array $data, AffiliateService $service): void {
                        $service->markWithdrawalPaid($record, $data['payment_reference'], $data['admin_notes'] ?? null);
                        Notification::make()->title('Withdrawal marked as paid — affiliate notified')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AffiliateWithdrawal $r): bool => in_array($r->status, ['pending', 'approved'], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')->required()->rows(3)
                            ->helperText('Shared with the affiliate in the rejection email.'),
                    ])
                    ->action(function (AffiliateWithdrawal $record, array $data, AffiliateService $service): void {
                        $service->rejectWithdrawal($record, $data['reason']);
                        Notification::make()->title('Withdrawal rejected — funds returned to affiliate')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateWithdrawals::route('/'),
            'view' => Pages\ViewAffiliateWithdrawal::route('/{record}'),
        ];
    }
}
