<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'All Products';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('variants');
    }

    public static function form(Form $form): Form
    {
        return static::configureForm($form, null);
    }

    public static function table(Table $table): Table
    {
        return static::configureTable($table, null);
    }

    /**
     * Shared product form. When $lockedSubsidiary is set, the subsidiary is
     * fixed (used by the Collections / Gadgets shortcut resources).
     */
    public static function configureForm(Form $form, ?string $lockedSubsidiary): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Variant mode')
                ->schema([
                    Forms\Components\Toggle::make('has_variants')
                        ->label('This product has variants')
                        ->helperText('Turn on for products sold in options like size or colour. Customers must pick a variant before adding to cart.')
                        ->live()
                        ->default(false),
                ]),

            Forms\Components\Section::make('Product details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
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
                        ->unique(ignoreRecord: true),
                    $lockedSubsidiary === null
                        ? Forms\Components\Select::make('subsidiary')
                            ->required()
                            ->options(Product::SUBSIDIARIES)
                        : Forms\Components\Hidden::make('subsidiary')
                            ->default($lockedSubsidiary),
                    Forms\Components\TextInput::make('category')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Tops, Smartphones'),
                    Forms\Components\TextInput::make('short_description')
                        ->required()
                        ->maxLength(200)
                        ->helperText('Up to 200 characters.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Pricing')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('base_price')
                        ->required()
                        ->numeric()
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('compare_price')
                        ->numeric()
                        ->prefix('₦')
                        ->helperText('Optional — shown struck through to indicate a discount.'),
                ]),

            Forms\Components\Section::make('Affiliate pricing (optional)')
                ->description('Set both fields and the product becomes eligible for affiliate commission. Affiliates buying with their code will see the affiliate price; you pay the commission per unit sold once delivered.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('affiliate_price')
                        ->numeric()
                        ->prefix('₦')
                        ->helperText('Discounted price shown when an affiliate code is applied. Must be ≤ base price.'),
                    Forms\Components\TextInput::make('affiliate_commission')
                        ->numeric()
                        ->prefix('₦')
                        ->helperText('Fixed amount paid to the affiliate for each unit sold.'),
                ])
                ->visible(fn (Get $get): bool => ! $get('has_variants')),

            Forms\Components\Section::make('Description & images')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->helperText('Product images. Drag to reorder — the first image is the cover.'),
                ]),

            Forms\Components\Section::make('Stock')
                ->visible(fn (Get $get): bool => ! $get('has_variants'))
                ->schema([
                    Forms\Components\TextInput::make('stock')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(fn (Get $get): bool => ! $get('has_variants')),
                ]),

            Forms\Components\Section::make('Variants')
                ->visible(fn (Get $get): bool => (bool) $get('has_variants'))
                ->schema([
                    Forms\Components\Repeater::make('attribute_keys')
                        ->label('Attribute keys')
                        ->simple(
                            Forms\Components\TextInput::make('key')
                                ->required()
                                ->placeholder('e.g. size, color'),
                        )
                        ->minItems(1)
                        ->helperText('Define which attributes apply to this product (e.g. size, color).'),

                    Forms\Components\Repeater::make('variants')
                        ->relationship()
                        ->columns(3)
                        ->minItems(1)
                        ->itemLabel(fn (array $state): string => collect($state['attributes'] ?? [])
                            ->values()
                            ->implode(' / ') ?: 'New variant')
                        ->helperText('At least one variant must have stock available.')
                        ->rule(static function (): Closure {
                            return static function (string $attribute, mixed $value, Closure $fail): void {
                                $hasStock = collect($value)
                                    ->contains(fn ($variant): bool => (int) ($variant['stock'] ?? 0) > 0);

                                if (! $hasStock) {
                                    $fail('At least one variant must have stock available.');
                                }
                            };
                        })
                        ->schema([
                            Forms\Components\KeyValue::make('attributes')
                                ->required()
                                ->keyLabel('Attribute')
                                ->valueLabel('Value')
                                ->helperText('Use the attribute keys defined above (e.g. size, color).')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('sku')
                                ->maxLength(255)
                                ->helperText('Optional.'),
                            Forms\Components\TextInput::make('price_override')
                                ->numeric()
                                ->prefix('₦')
                                ->helperText('Optional — blank uses the base price.'),
                            Forms\Components\TextInput::make('stock')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            Forms\Components\TextInput::make('affiliate_price')
                                ->numeric()
                                ->prefix('₦')
                                ->helperText('Optional affiliate price for this variant.'),
                            Forms\Components\TextInput::make('affiliate_commission')
                                ->numeric()
                                ->prefix('₦')
                                ->helperText('Optional commission per unit for this variant.'),
                        ]),
                ]),

            Forms\Components\Section::make('Installment plans (optional)')
                ->description('Only applies to gadgets. When enabled customers can buy this product (or variant) in installments.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('installment_enabled')
                        ->label('Enable installment plans')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('installment_minimum_down_payment_percentage')
                        ->label('Minimum down payment %')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->suffix('%')
                        ->visible(fn (Get $get): bool => (bool) $get('installment_enabled')),
                    Forms\Components\TextInput::make('installment_maximum_length_months')
                        ->label('Maximum length (months)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(36)
                        ->suffix('months')
                        ->visible(fn (Get $get): bool => (bool) $get('installment_enabled')),
                ]),

            Forms\Components\Section::make('Visibility')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->inline(false),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured')
                        ->inline(false),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                ]),
        ]);
    }

    /**
     * Shared product table. $subsidiaryContext is informational only — the
     * shortcut resources scope their query via getEloquentQuery().
     */
    public static function configureTable(Table $table, ?string $subsidiaryContext): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->label('Image')
                    ->limit(1)
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->category),
                Tables\Columns\TextColumn::make('subsidiary')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Product::SUBSIDIARIES[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'gadgets' ? 'info' : 'warning')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('display_price')
                    ->label('Price')
                    ->money('NGN')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('base_price', $direction)),
                Tables\Columns\IconColumn::make('has_variants')
                    ->label('Variants')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_in_stock')
                    ->label('In stock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn (): array => Product::query()
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn (Collection $records) => static::bulkUpdate($records, ['is_published' => true], 'published'))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->action(fn (Collection $records) => static::bulkUpdate($records, ['is_published' => false], 'unpublished'))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Collection $records) => static::bulkUpdate($records, ['is_featured' => true], 'featured'))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Unfeature')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(fn (Collection $records) => static::bulkUpdate($records, ['is_featured' => false], 'unfeatured'))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    protected static function bulkUpdate(Collection $records, array $values, string $verb): void
    {
        $records->each->update($values);

        Notification::make()
            ->title($records->count().' product(s) '.$verb)
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
