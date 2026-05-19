<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Internal notes';

    protected static ?string $recordTitleAttribute = 'body';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('body')
                ->required()
                ->rows(3)
                ->helperText('Internal — never shown to the customer.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('body')
                    ->wrap()
                    ->limit(120),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Added by')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add note')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
