<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Mail\InspectionApprovedCustomer;
use App\Mail\InspectionRejectedCustomer;
use App\Models\Animal;
use App\Models\Inspection;
use App\Models\Land;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Inspections';

    protected static ?int $navigationSort = 1;

    private const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
        'no_show' => 'No-Show',
    ];

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('inspectable');
    }

    /**
     * Admin edit URL for whatever is being inspected (Land or Animal).
     */
    public static function inspectableAdminUrl(?Model $inspectable): ?string
    {
        return match (true) {
            $inspectable instanceof Land => LandResource::getUrl('edit', ['record' => $inspectable]),
            $inspectable instanceof Animal => AnimalResource::getUrl('edit', ['record' => $inspectable]),
            default => null,
        };
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('party_size')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('customer_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('customer_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('customer_phone')
                        ->required()
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Schedule')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('preferred_date')
                        ->required(),
                    Forms\Components\TextInput::make('preferred_time_slot')
                        ->required(),
                    Forms\Components\DatePicker::make('alternate_date'),
                    Forms\Components\Textarea::make('notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Status & meeting details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->required()
                        ->options(self::STATUS_OPTIONS),
                    Forms\Components\Textarea::make('meeting_address')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Revealed to the customer only once the status is Approved.'),
                    Forms\Components\Textarea::make('meeting_instructions')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('admin_notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Internal notes. Also used as the reason in rejection emails.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('inspectable_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => $state === Animal::class ? 'info' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                Tables\Columns\TextColumn::make('inspectable')
                    ->label('Listing')
                    ->getStateUsing(fn (Inspection $record): string => $record->inspectable?->title ?? '—')
                    ->url(fn (Inspection $record): ?string => self::inspectableAdminUrl($record->inspectable))
                    ->color('primary')
                    ->searchable(false),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Inspection $record): string => $record->customer_phone),
                Tables\Columns\TextColumn::make('preferred_date')
                    ->date()
                    ->sortable()
                    ->description(fn (Inspection $record): string => $record->preferred_time_slot),
                Tables\Columns\TextColumn::make('party_size')
                    ->label('Party')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'completed' => 'info',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::STATUS_OPTIONS[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::STATUS_OPTIONS)
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Inspection $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('meeting_address')
                            ->required()
                            ->rows(3)
                            ->helperText('Sent to the customer in the approval email.'),
                        Forms\Components\Textarea::make('meeting_instructions')
                            ->rows(3),
                    ])
                    ->action(function (Inspection $record, array $data): void {
                        $record->update([
                            'status' => 'approved',
                            'meeting_address' => $data['meeting_address'],
                            'meeting_instructions' => $data['meeting_instructions'] ?? null,
                        ]);
                        $record->loadMissing('inspectable');

                        Mail::to($record->customer_email)->send(new InspectionApprovedCustomer($record));

                        Notification::make()
                            ->title('Inspection approved — customer notified')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Inspection $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Reason')
                            ->required()
                            ->rows(3)
                            ->helperText('Shared with the customer in the rejection email.'),
                    ])
                    ->action(function (Inspection $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                        $record->loadMissing('inspectable');

                        Mail::to($record->customer_email)->send(new InspectionRejectedCustomer($record));

                        Notification::make()
                            ->title('Inspection rejected — customer notified')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-flag')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Inspection $record): bool => in_array($record->status, ['pending', 'approved'], true))
                    ->action(function (Inspection $record): void {
                        $record->update(['status' => 'completed']);

                        Notification::make()->title('Marked as completed')->success()->send();
                    }),

                Tables\Actions\Action::make('markNoShow')
                    ->label('Mark No-Show')
                    ->icon('heroicon-o-user-minus')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Inspection $record): bool => in_array($record->status, ['pending', 'approved'], true))
                    ->action(function (Inspection $record): void {
                        $record->update(['status' => 'no_show']);

                        Notification::make()->title('Marked as no-show')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkApprove')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Textarea::make('meeting_address')
                                ->required()
                                ->rows(3)
                                ->helperText('Applied to every selected pending inspection — best used for inspections of the same land.'),
                            Forms\Components\Textarea::make('meeting_instructions')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $approved = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    continue;
                                }

                                $record->update([
                                    'status' => 'approved',
                                    'meeting_address' => $data['meeting_address'],
                                    'meeting_instructions' => $data['meeting_instructions'] ?? null,
                                ]);
                                $record->loadMissing('inspectable');

                                Mail::to($record->customer_email)->send(new InspectionApprovedCustomer($record));
                                $approved++;
                            }

                            Notification::make()
                                ->title("{$approved} inspection(s) approved — customers notified")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
        ];
    }
}
