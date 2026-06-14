<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandResource\Pages;
use App\Models\Land;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LandResource extends Resource
{
    protected static ?string $model = Land::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Real Estate';

    protected static ?string $navigationLabel = 'Real Estate';

    protected static ?string $modelLabel = 'property';

    protected static ?string $pluralModelLabel = 'properties';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Listing details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                            if ($operation === 'create' && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Used in the public URL: /lands/{slug}'),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->options([
                            'available' => 'Available',
                            'reserved' => 'Reserved',
                            'reserved_installment' => 'Reserved (installment)',
                            'sold' => 'Sold',
                        ])
                        ->default('available')
                        ->helperText('"Reserved (installment)" is set automatically when a customer activates an installment plan.'),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured on the homepage')
                        ->inline(false),
                ]),

            Forms\Components\Section::make('Location')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('location_address')
                        ->label('Full address')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('state')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('city_or_lga')
                        ->label('City / LGA')
                        ->required()
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Plots & pricing')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('number_of_plots')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    Forms\Components\TextInput::make('plot_size_sqm')
                        ->label('Plot size (sqm)')
                        ->required()
                        ->numeric()
                        ->default(648),
                    Forms\Components\TextInput::make('price_per_plot')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('price_label')
                        ->maxLength(255)
                        ->placeholder('e.g. Negotiable, Starting from'),
                ]),

            Forms\Components\Section::make('Description & features')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5),
                    Forms\Components\TagsInput::make('features')
                        ->placeholder('Add a feature')
                        ->helperText('Press Enter after each feature.'),
                    Forms\Components\TagsInput::make('close_to_landmarks')
                        ->label('Close to landmarks')
                        ->placeholder('Add a landmark')
                        ->helperText('Nearby places of interest.'),
                ]),

            Forms\Components\Section::make('Land qualities & documents')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('has_good_access_road')
                        ->default(true),
                    Forms\Components\Toggle::make('is_flood_free')
                        ->default(true),
                    Forms\Components\Toggle::make('installment_available')
                        ->default(false),
                    Forms\Components\CheckboxList::make('document_status')
                        ->label('Documents in place')
                        ->options(Land::DOCUMENT_TYPES)
                        ->columns(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Installment plans (optional)')
                ->description('When enabled, customers can buy this land in installments.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('installment_enabled')
                        ->label('Enable installment plans for this land')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('installment_minimum_down_payment_percentage')
                        ->label('Minimum down payment %')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->suffix('%')
                        ->required(fn (\Filament\Forms\Get $get): bool => (bool) $get('installment_enabled'))
                        ->visible(fn (\Filament\Forms\Get $get): bool => (bool) $get('installment_enabled')),
                    Forms\Components\TextInput::make('installment_maximum_length_months')
                        ->label('Maximum length (months)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(36)
                        ->suffix('months')
                        ->required(fn (\Filament\Forms\Get $get): bool => (bool) $get('installment_enabled'))
                        ->visible(fn (\Filament\Forms\Get $get): bool => (bool) $get('installment_enabled')),
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Land $record): string => $record->city_or_lga.', '.$record->state),
                Tables\Columns\TextColumn::make('state')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('number_of_plots')
                    ->label('Plots')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_plot')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('installment_available')
                    ->label('Installment')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved', 'reserved_installment' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $s): string => match ($s) {
                        'reserved_installment' => 'Reserved (installment)',
                        default => ucfirst($s),
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'reserved' => 'Reserved',
                        'reserved_installment' => 'Reserved (installment)',
                        'sold' => 'Sold',
                    ]),
                Tables\Filters\SelectFilter::make('state')
                    ->options(fn (): array => Land::query()
                        ->distinct()
                        ->orderBy('state')
                        ->pluck('state', 'state')
                        ->all()),
                Tables\Filters\TernaryFilter::make('installment_available')
                    ->label('Installment available'),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('This creates an editable copy of the land, including its images.')
                    ->action(function (Land $record) {
                        $copy = $record->replicate();
                        $copy->title = $record->title.' (Copy)';
                        $copy->slug = Land::uniqueSlug($copy->title);
                        $copy->status = 'available';
                        $copy->is_featured = false;
                        $copy->save();

                        foreach (['cover', 'gallery'] as $collection) {
                            foreach ($record->getMedia($collection) as $media) {
                                $media->copy($copy, $collection);
                            }
                        }

                        Notification::make()
                            ->title('Land duplicated')
                            ->success()
                            ->send();

                        return redirect(LandResource::getUrl('edit', ['record' => $copy]));
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->required()
                                ->options([
                                    'available' => 'Available',
                                    'reserved' => 'Reserved',
                                    'sold' => 'Sold',
                                ]),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['status' => $data['status']]);

                            Notification::make()
                                ->title($records->count().' land(s) updated')
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
            'index' => Pages\ListLands::route('/'),
            'create' => Pages\CreateLand::route('/create'),
            'edit' => Pages\EditLand::route('/{record}/edit'),
        ];
    }
}
