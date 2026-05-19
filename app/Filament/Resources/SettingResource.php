<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->disabledOn('edit')
                ->helperText('A unique identifier — cannot be changed once created.'),
            Forms\Components\Textarea::make('value')
                ->required()
                ->rows(3)
                ->helperText('Comma-separated values are read as lists (e.g. inspection days, time slots).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('value')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
