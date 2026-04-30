<?php

namespace App\Filament\Admin\Resources\Waitlists\Tables;

use App\Jobs\NotifyWaitlist;
use App\Models\Product;
use App\Models\Waitlist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WaitlistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->default('Guest')
                    ->searchable(),
                TextColumn::make('waitlistable_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => ucfirst(class_basename($state))),
                TextColumn::make('waitlistable_id')
                    ->label('Item ID'),
                TextColumn::make('notified_at')
                    ->label('Notified')
                    ->since()
                    ->default('Not yet'),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('waitlistable_type')
                    ->label('Type')
                    ->options([
                        'product' => 'Products',
                        'activity' => 'Activities',
                        'accommodation' => 'Accommodations',
                        'vehicle' => 'Vehicles',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([
                Action::make('notifyAll')
                    ->label('Notify All Pending')
                    ->icon('heroicon-o-bell')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $productIds = Waitlist::where('waitlistable_type', 'product')
                            ->whereNull('notified_at')
                            ->distinct()
                            ->pluck('waitlistable_id');

                        foreach ($productIds as $id) {
                            $product = Product::find($id);
                            if ($product) {
                                NotifyWaitlist::dispatch($product);
                            }
                        }

                        Notification::make()->title('Waitlist notifications queued')->success()->send();
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
