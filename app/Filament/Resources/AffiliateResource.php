<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Models\Affiliate;
use App\Services\AffiliateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Affiliates';

    protected static ?string $navigationLabel = 'Affiliates';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', 'active')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affiliate')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('status')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('user.name')->label('Name')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('user.email')->label('Email')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('user.phone')->label('Phone')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('whatsapp_number')->disabled()->dehydrated(false),
                    Forms\Components\KeyValue::make('social_handles')->disabled()->dehydrated(false)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Bank details')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('bank_name')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('bank_account_number')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('bank_account_name')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Balances')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('pending_balance')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('available_balance')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('total_earned')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('total_withdrawn')->prefix('₦')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Suspension')
                ->visible(fn (?Affiliate $record): bool => (bool) $record?->isSuspended())
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('suspended_at')->disabled()->dehydrated(false),
                    Forms\Components\Textarea::make('suspension_reason')->disabled()->dehydrated(false)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('joined_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Affiliate')
                    ->searchable(['users.name', 'users.email'])
                    ->description(fn (Affiliate $r): ?string => $r->user?->email),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $s): string => $s === 'active' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('available_balance')->money('NGN')->sortable()->label('Available'),
                Tables\Columns\TextColumn::make('pending_balance')->money('NGN')->sortable()->label('Pending'),
                Tables\Columns\TextColumn::make('total_earned')->money('NGN')->sortable()->label('Lifetime'),
                Tables\Columns\TextColumn::make('joined_at')->label('Joined')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'suspended' => 'Suspended']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Affiliate $r): bool => $r->isActive())
                    ->form([
                        Forms\Components\Textarea::make('reason')->required()->rows(3),
                    ])
                    ->action(function (Affiliate $record, array $data, AffiliateService $service): void {
                        $service->suspend($record, $data['reason']);
                        Notification::make()->title('Affiliate suspended')->success()->send();
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Affiliate $r): bool => $r->isSuspended())
                    ->requiresConfirmation()
                    ->action(function (Affiliate $record, AffiliateService $service): void {
                        $service->reactivate($record);
                        Notification::make()->title('Affiliate reactivated')->success()->send();
                    }),
                Tables\Actions\Action::make('credit')
                    ->label('Manual credit')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('₦')->minValue(1),
                        Forms\Components\Textarea::make('reason')->required()->rows(2),
                    ])
                    ->action(function (Affiliate $record, array $data, AffiliateService $service): void {
                        $service->adjustBalance($record, 'credit', (float) $data['amount'], $data['reason']);
                        Notification::make()->title('Balance credited')->success()->send();
                    }),
                Tables\Actions\Action::make('debit')
                    ->label('Manual debit')
                    ->icon('heroicon-o-minus-circle')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('₦')->minValue(1),
                        Forms\Components\Textarea::make('reason')->required()->rows(2),
                    ])
                    ->action(function (Affiliate $record, array $data, AffiliateService $service): void {
                        $service->adjustBalance($record, 'debit', (float) $data['amount'], $data['reason']);
                        Notification::make()->title('Balance debited')->success()->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AffiliateResource\RelationManagers\CommissionsRelationManager::class,
            AffiliateResource\RelationManagers\WithdrawalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliates::route('/'),
            'view' => Pages\ViewAffiliate::route('/{record}'),
        ];
    }
}
