<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimalResource\Pages;
use App\Models\Animal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AnimalResource extends Resource
{
    protected static ?string $model = Animal::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Kennel & Farm';

    protected static ?string $navigationLabel = 'Animals';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity & type')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('listing_type')
                        ->required()
                        ->live()
                        ->options([
                            'individual' => 'Individual (dogs, cats)',
                            'pool' => 'Pool (rabbits, grasscutters)',
                        ])
                        ->helperText('Individuals are sold one by one; pools are sold from stock.'),
                    Forms\Components\Select::make('category')
                        ->required()
                        ->options(Animal::CATEGORIES),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                            if ($operation === 'create' && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->helperText('A name for individuals (e.g. "Rex"), or a label for pools (e.g. "Standard Rabbit").'),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('breed')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('sex')
                        ->options(fn (Get $get): array => $get('listing_type') === 'pool'
                            ? ['male' => 'Male', 'female' => 'Female', 'mixed' => 'Mixed']
                            : ['male' => 'Male', 'female' => 'Female'])
                        ->required(fn (Get $get): bool => $get('listing_type') === 'individual'),
                    Forms\Components\TextInput::make('origin')
                        ->maxLength(255)
                        ->placeholder('e.g. Imported from Germany, Locally bred')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Individual details')
                ->columns(2)
                ->visible(fn (Get $get): bool => $get('listing_type') === 'individual')
                ->schema([
                    Forms\Components\Select::make('availability')
                        ->options([
                            'available' => 'Available',
                            'reserved' => 'Reserved',
                            'sold' => 'Sold',
                            'on_order' => 'On Order',
                        ])
                        ->default('available')
                        ->required(fn (Get $get): bool => $get('listing_type') === 'individual'),
                    Forms\Components\TextInput::make('age_text')
                        ->label('Age')
                        ->maxLength(255)
                        ->placeholder('e.g. 3 months'),
                    Forms\Components\DatePicker::make('date_of_birth'),
                    Forms\Components\TextInput::make('temperament')
                        ->maxLength(255)
                        ->placeholder('e.g. Calm and friendly'),
                    Forms\Components\Textarea::make('parents_info')
                        ->label('Parents info')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Pool details')
                ->columns(2)
                ->visible(fn (Get $get): bool => $get('listing_type') === 'pool')
                ->schema([
                    Forms\Components\TextInput::make('stock')
                        ->required(fn (Get $get): bool => $get('listing_type') === 'pool')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Number of animals currently available in this pool.'),
                ]),

            Forms\Components\Section::make('Description & traits')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5),
                    Forms\Components\TextInput::make('typical_adult_size')
                        ->maxLength(255)
                        ->placeholder('e.g. Medium, 20-30kg'),
                    Forms\Components\TagsInput::make('highlights')
                        ->placeholder('Add a highlight')
                        ->helperText('Press Enter after each highlight.'),
                ]),

            Forms\Components\Section::make('Health')
                ->schema([
                    Forms\Components\CheckboxList::make('vaccination_status')
                        ->label('Vaccination status')
                        ->options(Animal::VACCINATION_TYPES)
                        ->columns(2),
                ]),

            Forms\Components\Section::make('Pricing & visibility')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                    Forms\Components\Toggle::make('supports_inspection')
                        ->default(true),
                    Forms\Components\Toggle::make('supports_online_purchase')
                        ->default(true),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured')
                        ->inline(false),
                ]),

            Forms\Components\Section::make('Affiliate pricing (optional)')
                ->description('Set both fields and the animal becomes eligible for affiliate commission.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('affiliate_price')
                        ->numeric()
                        ->prefix('₦')
                        ->helperText('Affiliate-only price. Must be ≤ regular price.'),
                    Forms\Components\TextInput::make('affiliate_commission')
                        ->numeric()
                        ->prefix('₦')
                        ->helperText('Fixed amount paid to the affiliate per sale.'),
                ]),

            Forms\Components\Section::make('Images')
                ->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                        ->collection('cover')
                        ->image()
                        ->helperText('The main cover image shown on listing cards.'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                        ->collection('gallery')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->helperText('Additional gallery images. Drag to reorder.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')
                    ->label('Cover')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Animal $record): string => $record->breed),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Animal::CATEGORIES[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('listing_type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'pool' ? 'info' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_availability')
                    ->label('Availability')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved', 'on_order' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(Animal::CATEGORIES),
                Tables\Filters\SelectFilter::make('listing_type')
                    ->options([
                        'individual' => 'Individual',
                        'pool' => 'Pool',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Creates an editable copy — handy for restocking pool listings.')
                    ->action(function (Animal $record) {
                        $copy = $record->replicate();
                        $copy->name = $record->name.' (Copy)';
                        $copy->slug = Animal::uniqueSlug($copy->name);
                        $copy->is_featured = false;
                        $copy->save();

                        foreach (['cover', 'gallery'] as $collection) {
                            foreach ($record->getMedia($collection) as $media) {
                                $media->copy($copy, $collection);
                            }
                        }

                        Notification::make()
                            ->title('Animal duplicated')
                            ->success()
                            ->send();

                        return redirect(AnimalResource::getUrl('edit', ['record' => $copy]));
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markSold')
                        ->label('Mark as sold')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->listing_type === 'pool') {
                                    $record->update(['stock' => 0]);
                                } else {
                                    $record->update(['availability' => 'sold']);
                                }
                            }

                            Notification::make()
                                ->title($records->count().' animal(s) marked as sold')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markReserved')
                        ->label('Mark as reserved')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['availability' => 'reserved']);

                            Notification::make()
                                ->title($records->count().' animal(s) marked as reserved')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('toggleFeatured')
                        ->label('Toggle featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_featured' => ! $record->is_featured]);
                            }

                            Notification::make()
                                ->title($records->count().' animal(s) updated')
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
            'index' => Pages\ListAnimals::route('/'),
            'create' => Pages\CreateAnimal::route('/create'),
            'edit' => Pages\EditAnimal::route('/{record}/edit'),
        ];
    }
}
