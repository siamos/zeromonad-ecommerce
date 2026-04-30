<?php

namespace App\Filament\Manager\Resources\Referrals\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referrer.name')
                    ->label('Referrer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('referrer.email')
                    ->label('Referrer Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('referred.name')
                    ->label('Referred User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('reward_points')
                    ->label('Points Awarded')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('rewarded_at')
                    ->label('Rewarded')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
