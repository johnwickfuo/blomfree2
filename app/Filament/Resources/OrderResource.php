<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 1;

    private const STATUS_OPTIONS = [
        'pending_payment' => 'Pending payment',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ];

    private const ACTIONABLE = ['paid', 'processing'];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('items');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->whereIn('order_status', self::ACTIONABLE)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer & delivery')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('delivery_method')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('customer_name')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('customer_email')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('customer_phone')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('delivery_state')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('delivery_lga')->disabled()->dehydrated(false),
                    Forms\Components\Textarea::make('delivery_address')
                        ->rows(2)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('delivery_notes')
                        ->rows(2)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->disabled()
                        ->dehydrated(false)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(4)
                        ->schema([
                            Forms\Components\TextInput::make('item_name')->disabled(),
                            Forms\Components\TextInput::make('item_label')->disabled(),
                            Forms\Components\TextInput::make('quantity')->disabled(),
                            Forms\Components\TextInput::make('subtotal')->prefix('₦')->disabled(),
                        ]),
                ]),

            Forms\Components\Section::make('Totals & payment')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('shipping_fee')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('total')->prefix('₦')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('payment_gateway')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('payment_status')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('payment_reference')->disabled()->dehydrated(false),
                ]),

            Forms\Components\Section::make('Status & notes')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('order_status')
                        ->required()
                        ->options(self::STATUS_OPTIONS),
                    Forms\Components\Textarea::make('tracking_notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Shared with the customer in the "Shipped" email.'),
                    Forms\Components\Textarea::make('admin_notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Internal — never sent to the customer.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->recordAction('view')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_email', 'customer_phone'])
                    ->description(fn (Order $r): string => $r->customer_email),
                Tables\Columns\TextColumn::make('total')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_gateway')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('order_status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'paid', 'processing' => 'warning',
                        'shipped' => 'info',
                        'delivered' => 'success',
                        'cancelled', 'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $s): string => self::STATUS_OPTIONS[$s] ?? $s)
                    ->sortable(),
                Tables\Columns\TextColumn::make('placed_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')
                    ->options(self::STATUS_OPTIONS)
                    ->multiple()
                    ->default(['paid', 'processing']),
                Tables\Filters\SelectFilter::make('payment_gateway')
                    ->options(['paystack' => 'Paystack', 'flutterwave' => 'Flutterwave']),
            ])
            ->actions([
                Tables\Actions\Action::make('markProcessing')
                    ->label('Mark Processing')
                    ->icon('heroicon-o-cog')
                    ->color('warning')
                    ->visible(fn (Order $r): bool => $r->order_status === 'paid')
                    ->requiresConfirmation()
                    ->action(function (Order $record, OrderService $orders): void {
                        $orders->markStatus($record, 'processing');
                        Notification::make()->title('Order marked as processing')->success()->send();
                    }),

                Tables\Actions\Action::make('markShipped')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $r): bool => in_array($r->order_status, ['paid', 'processing'], true))
                    ->form([
                        Forms\Components\Textarea::make('tracking_notes')
                            ->required()
                            ->rows(3)
                            ->helperText('Sent to the customer in the shipped notification.'),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orders): void {
                        $orders->markStatus($record, 'shipped', $data['tracking_notes']);
                        Notification::make()->title('Order marked as shipped — customer notified')->success()->send();
                    }),

                Tables\Actions\Action::make('markDelivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $r): bool => $r->order_status === 'shipped')
                    ->requiresConfirmation()
                    ->action(function (Order $record, OrderService $orders): void {
                        $orders->markStatus($record, 'delivered');
                        Notification::make()->title('Order marked as delivered — customer notified')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $r): bool => ! in_array($r->order_status, ['cancelled', 'refunded', 'delivered'], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3)
                            ->helperText('Shared with the customer in the cancellation email.'),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orders): void {
                        $orders->cancel($record, $data['reason']);
                        Notification::make()->title('Order cancelled — items restocked, customer notified')->success()->send();
                    }),

                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-receipt-refund')
                    ->color('gray')
                    ->visible(fn (Order $r): bool => $r->payment_status === 'paid' && $r->order_status !== 'refunded')
                    ->form(fn (Order $record): array => [
                        Forms\Components\TextInput::make('amount')
                            ->label('Refund amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₦')
                            ->default((float) $record->total)
                            ->helperText('Amount being refunded — used for the audit trail.'),
                        Forms\Components\Textarea::make('notes')
                            ->required()
                            ->rows(3)
                            ->helperText('Reason / context — shared with the customer in the email.'),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orders): void {
                        $orders->markRefunded($record, (float) $data['amount'], $data['notes']);
                        Notification::make()->title('Order marked as refunded — items restocked, refund logged')->success()->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkProcessing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-cog')
                        ->color('warning')
                        ->action(function (Collection $records, OrderService $orders): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->order_status === 'paid') {
                                    $orders->markStatus($record, 'processing');
                                    $count++;
                                }
                            }
                            Notification::make()->title("{$count} order(s) moved to processing")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('bulkShipped')
                        ->label('Mark as Shipped')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('tracking_notes')
                                ->required()
                                ->rows(3)
                                ->helperText('Applied to every selected order.'),
                        ])
                        ->action(function (Collection $records, array $data, OrderService $orders): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (in_array($record->order_status, ['paid', 'processing'], true)) {
                                    $orders->markStatus($record, 'shipped', $data['tracking_notes']);
                                    $count++;
                                }
                            }
                            Notification::make()->title("{$count} order(s) marked as shipped")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderResource\RelationManagers\NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
