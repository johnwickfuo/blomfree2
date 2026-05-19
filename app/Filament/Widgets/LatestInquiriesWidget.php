<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInquiriesWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Latest unread inquiries';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inquiry::query()
                    ->where('status', 'new')
                    ->latest()
                    ->limit(5),
            )
            ->emptyStateHeading('No unread inquiries')
            ->emptyStateDescription('All caught up.')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->since(),
                Tables\Columns\TextColumn::make('name')
                    ->label('From')
                    ->description(fn (Inquiry $r): string => $r->email),
                Tables\Columns\TextColumn::make('subject')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::SUBJECTS[$state] ?? $state),
                Tables\Columns\TextColumn::make('message')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Inquiry $r): string => InquiryResource::getUrl('edit', ['record' => $r])),
            ]);
    }
}
