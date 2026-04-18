<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-shopping-bag'; }
    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make('Order Info')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'paid'       => 'Paid',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                        'refunded'   => 'Refunded',
                    ])->required(),
                Forms\Components\Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded'])
                    ->required(),
                Forms\Components\TextInput::make('payment_method')->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Amounts')->schema([
                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('€')->disabled(),
                Forms\Components\TextInput::make('total')->numeric()->prefix('€')->disabled(),
            ])->columns(4),

            Forms\Components\Section::make('Billing Address')->schema([
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
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning'  => 'pending',
                        'primary'  => 'processing',
                        'success'  => fn ($state) => in_array($state, ['paid', 'delivered', 'shipped']),
                        'danger'   => fn ($state) => in_array($state, ['cancelled', 'refunded']),
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors(['success' => 'paid', 'warning' => 'unpaid', 'gray' => 'refunded']),
                Tables\Columns\TextColumn::make('payment_method')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('total')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending'    => 'Pending',
                    'processing' => 'Processing',
                    'paid'       => 'Paid',
                    'shipped'    => 'Shipped',
                    'delivered'  => 'Delivered',
                    'cancelled'  => 'Cancelled',
                    'refunded'   => 'Refunded',
                ]),
                SelectFilter::make('payment_status')->options([
                    'unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_method')->options([
                    'cash'          => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'viva_wallet'   => 'Viva Wallet',
                    'cardlink'      => 'Cardlink',
                    'stripe'        => 'Stripe',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
