<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Inquiries';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $unread = static::getModel()::query()->where('status', 'new')->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('From')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('subject')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?string $state): string => Inquiry::SUBJECTS[$state] ?? (string) $state),
                    Forms\Components\TextInput::make('email')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('phone')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('related_url')
                        ->label('Came from')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Message')
                ->schema([
                    Forms\Components\Textarea::make('message')
                        ->rows(6)
                        ->disabled()
                        ->dehydrated(false),
                ]),

            Forms\Components\Section::make('Status & internal notes')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->required()
                        ->options([
                            'new' => 'New',
                            'in_progress' => 'In progress',
                            'responded' => 'Responded',
                            'closed' => 'Closed',
                        ]),
                    Forms\Components\DateTimePicker::make('responded_at')
                        ->label('Responded at'),
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
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('From')
                    ->searchable(['name', 'email', 'phone'])
                    ->description(fn (Inquiry $r): string => $r->email),
                Tables\Columns\TextColumn::make('subject')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'partnership' || $state === 'media' ? 'info' : 'gray')
                    ->formatStateUsing(fn (string $state): string => Inquiry::SUBJECTS[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'in_progress' => 'info',
                        'responded' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('responded_at')
                    ->dateTime('M j, H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'in_progress' => 'In progress',
                        'responded' => 'Responded',
                        'closed' => 'Closed',
                    ])
                    ->multiple()
                    ->default(['new']),
                Tables\Filters\SelectFilter::make('subject')
                    ->options(Inquiry::SUBJECTS),
            ])
            ->actions([
                Tables\Actions\Action::make('quickRespond')
                    ->label('Quick respond')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->url(function (Inquiry $record): string {
                        $subject = 'Re: '.($record->subjectLabel());
                        $body = "Hi {$record->name},\n\nThanks for reaching out to BLOMFREE & CO.\n\n";

                        return 'mailto:'.$record->email
                            .'?subject='.rawurlencode($subject)
                            .'&body='.rawurlencode($body);
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('markResponded')
                    ->label('Mark responded')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Inquiry $r): bool => $r->status !== 'responded' && $r->status !== 'closed')
                    ->requiresConfirmation()
                    ->action(function (Inquiry $record): void {
                        $record->update([
                            'status' => 'responded',
                            'responded_at' => now(),
                        ]);

                        Notification::make()->title('Marked as responded')->success()->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markInProgress')
                        ->label('Mark in progress')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'in_progress']);

                            Notification::make()->title($records->count().' inquiry(ies) marked in progress')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markClosed')
                        ->label('Mark closed')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'closed']);

                            Notification::make()->title($records->count().' inquiry(ies) closed')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
