<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages;
use App\Models\ShippingZone;
use App\Support\NigerianStates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Shipping Zones';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Zone details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g. Bayelsa, Neighboring South'),
                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₦'),
                    Forms\Components\TextInput::make('delivery_estimate_days')
                        ->label('Delivery estimate')
                        ->maxLength(255)
                        ->placeholder('e.g. 1-2 days, 3-5 days'),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers win when a state appears in multiple active zones.'),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->inline(false),
                ]),

            Forms\Components\Section::make('States covered')
                ->schema([
                    Forms\Components\Select::make('states')
                        ->required()
                        ->multiple()
                        ->searchable()
                        ->options(NigerianStates::forSelect())
                        ->helperText('Each state should normally belong to one active zone — overlaps will be flagged when you save.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('states')
                    ->label('States')
                    ->getStateUsing(fn (ShippingZone $record): int => count($record->states ?? []))
                    ->suffix(' covered'),
                Tables\Columns\TextColumn::make('delivery_estimate_days')
                    ->label('Estimate')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Non-blocking warning: highlight any states in this zone that also appear
     * in another active zone, so the admin can fix the overlap.
     */
    public static function warnAboutOverlaps(ShippingZone $zone): void
    {
        if (! $zone->is_active) {
            return;
        }

        $overlaps = ShippingZone::query()
            ->where('is_active', true)
            ->whereKeyNot($zone->getKey())
            ->get()
            ->mapWithKeys(function (ShippingZone $other) use ($zone): array {
                $shared = array_values(array_intersect(
                    $zone->states ?? [],
                    $other->states ?? [],
                ));

                return $shared ? [$other->name => $shared] : [];
            })
            ->filter();

        if ($overlaps->isEmpty()) {
            return;
        }

        $body = $overlaps
            ->map(fn (array $states, string $name): string => $name.': '.implode(', ', $states))
            ->values()
            ->implode("\n");

        Notification::make()
            ->title('State overlap detected')
            ->body($body)
            ->warning()
            ->persistent()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingZones::route('/'),
            'create' => Pages\CreateShippingZone::route('/create'),
            'edit' => Pages\EditShippingZone::route('/{record}/edit'),
        ];
    }
}
