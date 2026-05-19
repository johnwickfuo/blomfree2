<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPlanResource\Pages;
use App\Models\InstallmentPlan;
use App\Services\InstallmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentPlanResource extends Resource
{
    protected static ?string $model = InstallmentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Installments';

    protected static ?string $navigationLabel = 'Plans';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', 'pending_approval')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Plan')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('reference')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('status')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('subsidiary')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('installable_label')->disabled()->dehydrated(false)->columnSpanFull(),
                    Forms\Components\TextInput::make('user.name')->label('Customer')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('user.email')->label('Email')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('user.phone')->label('Phone')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Financials')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('total_amount')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('amount_paid')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('minimum_down_payment_amount')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('forfeiture_percentage')->suffix('%')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('maximum_length_months')->suffix('months')->disabled()->dehydrated(false),
                    Forms\Components\DatePicker::make('deadline')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Affiliate')
                ->columns(3)
                ->visible(fn (?InstallmentPlan $record): bool => (bool) $record?->affiliate_id)
                ->schema([
                    Forms\Components\TextInput::make('affiliate_code_used')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('affiliate_commission_locked')->prefix('₦')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Suggested schedule')
                ->visible(fn (?InstallmentPlan $record): bool => (bool) $record?->suggested_schedule)
                ->schema([
                    Forms\Components\KeyValue::make('suggested_schedule')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Timeline')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('requested_at')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('approved_at')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('activated_at')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('completed_at')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('defaulted_at')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('cancelled_at')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('admin_notes')->rows(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(['users.name', 'users.email', 'users.phone'])
                    ->description(fn (InstallmentPlan $r): ?string => $r->user?->email),
                Tables\Columns\TextColumn::make('installable_label')->limit(40)->tooltip(fn (InstallmentPlan $r) => $r->installable_label),
                Tables\Columns\TextColumn::make('subsidiary')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'fulfilled' => 'success',
                        'active', 'awaiting_fulfillment' => 'info',
                        'pending_approval', 'awaiting_down_payment', 'completed', 'awaiting_refund' => 'warning',
                        'defaulted', 'cancelled_by_customer' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $s): string => str_replace('_', ' ', $s)),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid / Total')
                    ->money('NGN')
                    ->description(fn (InstallmentPlan $r): string => '₦'.number_format((float) $r->total_amount, 0)),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(InstallmentPlan::STATUSES, array_map(fn ($s) => str_replace('_', ' ', $s), InstallmentPlan::STATUSES)))
                    ->multiple(),
                Tables\Filters\SelectFilter::make('subsidiary')->options(['lands' => 'Lands', 'gadgets' => 'Gadgets']),
                Tables\Filters\Filter::make('deadline_overdue')
                    ->label('Overdue (deadline passed)')
                    ->query(fn ($q) => $q->whereDate('deadline', '<', today())->where('status', 'active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (InstallmentPlan $r): bool => $r->status === 'pending_approval')
                    ->form([Forms\Components\Textarea::make('admin_notes')->rows(2)])
                    ->action(function (InstallmentPlan $record, array $data, InstallmentService $service): void {
                        $service->approveLandPlan($record, null, $data['admin_notes'] ?? null);
                        Notification::make()->title('Plan approved — customer notified.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (InstallmentPlan $r): bool => $r->status === 'pending_approval')
                    ->form([Forms\Components\Textarea::make('reason')->required()->rows(2)])
                    ->action(function (InstallmentPlan $record, array $data, InstallmentService $service): void {
                        $service->rejectLandPlan($record, $data['reason']);
                        Notification::make()->title('Plan rejected.')->success()->send();
                    }),
                Tables\Actions\Action::make('record_offline_payment')
                    ->label('Record offline payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (InstallmentPlan $r): bool => in_array($r->status, ['awaiting_down_payment', 'active'], true))
                    ->form([
                        Forms\Components\TextInput::make('amount')->required()->numeric()->prefix('₦'),
                        Forms\Components\TextInput::make('reference')->required()->label('Bank/Reference'),
                        Forms\Components\Textarea::make('notes')->rows(2),
                    ])
                    ->action(function (InstallmentPlan $record, array $data, InstallmentService $service): void {
                        $service->recordPayment($record, [
                            'amount' => (float) $data['amount'],
                            'payment_gateway' => 'manual',
                            'payment_reference' => $data['reference'],
                            'gateway_response' => ['notes' => $data['notes'] ?? null],
                        ]);
                        Notification::make()->title('Offline payment recorded.')->success()->send();
                    }),
                Tables\Actions\Action::make('mark_fulfilled')
                    ->label('Mark fulfilled')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (InstallmentPlan $r): bool => $r->status === 'awaiting_fulfillment')
                    ->form([Forms\Components\Textarea::make('note')->label('Tracking / document reference')->rows(2)])
                    ->action(function (InstallmentPlan $record, array $data, InstallmentService $service): void {
                        $service->markFulfilled($record, $data['note'] ?? null);
                        Notification::make()->title('Plan fulfilled — customer notified.')->success()->send();
                    }),
                Tables\Actions\Action::make('trigger_refund')
                    ->label('Trigger refund')
                    ->icon('heroicon-o-receipt-refund')
                    ->color('danger')
                    ->visible(fn (InstallmentPlan $r): bool => in_array($r->status, ['defaulted', 'completed'], true) && ! $r->refund()->exists())
                    ->form([
                        Forms\Components\Select::make('triggered_by')
                            ->options([
                                'admin_override' => 'Admin override',
                                'land_resold' => 'Land resold',
                                'item_unavailable_at_completion' => 'Item unavailable at completion',
                            ])
                            ->required(),
                    ])
                    ->action(function (InstallmentPlan $record, array $data, InstallmentService $service): void {
                        $service->createRefund($record, $data['triggered_by']);
                        Notification::make()->title('Refund record created — process from Refunds.')->success()->send();
                    }),
                Tables\Actions\Action::make('force_default')
                    ->label('Force default')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (InstallmentPlan $r): bool => $r->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (InstallmentPlan $record, InstallmentService $service): void {
                        $service->markDefaulted($record);
                        Notification::make()->title('Plan defaulted.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentPlans::route('/'),
            'view' => Pages\ViewInstallmentPlan::route('/{record}'),
            'edit' => Pages\EditInstallmentPlan::route('/{record}/edit'),
        ];
    }
}
