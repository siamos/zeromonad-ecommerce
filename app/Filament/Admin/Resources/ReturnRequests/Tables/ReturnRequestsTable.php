<?php

namespace App\Filament\Admin\Resources\ReturnRequests\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReturnRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('reason')
                    ->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'requested' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'gray',
                        default => null,
                    }),
                TextColumn::make('refund_amount')
                    ->money('EUR')
                    ->placeholder('—')
                    ->label('Refund'),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Requested')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Requested',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
