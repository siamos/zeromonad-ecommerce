<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])->required(),
                Forms\Components\Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                    ->required(),
                Forms\Components\TextInput::make('total')->disabled()->prefix('€'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->default('Guest'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 'pending' => 'warning',
                        $state === 'processing' => 'primary',
                        in_array($state, ['paid', 'delivered', 'shipped']) => 'success',
                        in_array($state, ['cancelled', 'refunded']) => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'refunded' => 'gray',
                        default => null,
                    }),
                Tables\Columns\TextColumn::make('payment_method')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('total')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'processing' => 'Processing', 'paid' => 'Paid',
                    'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
