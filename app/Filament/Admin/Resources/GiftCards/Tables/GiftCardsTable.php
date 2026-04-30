<?php

namespace App\Filament\Admin\Resources\GiftCards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GiftCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('original_amount')
                    ->money('EUR')
                    ->label('Value'),
                TextColumn::make('remaining_balance')
                    ->money('EUR')
                    ->label('Balance'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('expires_at')
                    ->dateTime('d M Y')
                    ->placeholder('Never')
                    ->label('Expires'),
                TextColumn::make('issuedBy.name')
                    ->label('Issued By')
                    ->placeholder('—'),
                TextColumn::make('redeemedBy.name')
                    ->label('Used By')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
