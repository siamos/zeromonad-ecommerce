<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Orders & Customers';
    }

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Order Info')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])->required(),
                Forms\Components\Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                    ->required(),
                Forms\Components\TextInput::make('payment_method')->disabled(),
            ])->columns(2),

            Section::make('Amounts')->schema([
                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('total')->numeric()->prefix('€')->disabled(),
            ])->columns(4),

            Section::make('Billing Address')->schema([
                Forms\Components\KeyValue::make('billing_address')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
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
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'paid' => 'Paid',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                    'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_status')->options([
                    'unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_method')->options([
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'viva_wallet' => 'Viva Wallet',
                    'cardlink' => 'Cardlink',
                    'stripe' => 'Stripe',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
