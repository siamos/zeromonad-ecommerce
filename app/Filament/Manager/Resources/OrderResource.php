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
            Section::make('Order Details')->schema([
                Forms\Components\TextInput::make('order_number')->disabled()->dehydrated(false),
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
                Forms\Components\TextInput::make('payment_method')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('total')->disabled()->dehydrated(false)->prefix('€'),
                Forms\Components\TextInput::make('payment_gateway_transaction_id')
                    ->label('Transaction ID')
                    ->disabled()
                    ->dehydrated(false)
                    ->copyable(),
                Forms\Components\Textarea::make('notes')->disabled()->dehydrated(false)->columnSpanFull()->rows(2),
            ])->columns(2),

            Section::make('Billing Address')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('billing_address.name')->disabled()->dehydrated(false)->label('Name'),
                    Forms\Components\TextInput::make('billing_address.email')->disabled()->dehydrated(false)->label('Email'),
                    Forms\Components\TextInput::make('billing_address.phone')->disabled()->dehydrated(false)->label('Phone'),
                    Forms\Components\TextInput::make('billing_address.line1')->disabled()->dehydrated(false)->label('Address')->columnSpanFull(),
                    Forms\Components\TextInput::make('billing_address.city')->disabled()->dehydrated(false)->label('City'),
                    Forms\Components\TextInput::make('billing_address.zip')->disabled()->dehydrated(false)->label('Postcode'),
                ])->columns(2),

            Section::make('Shipping Address')
                ->collapsed()
                ->visible(fn ($record) => ! empty($record?->shipping_address))
                ->schema([
                    Forms\Components\TextInput::make('shipping_address.name')->disabled()->dehydrated(false)->label('Name'),
                    Forms\Components\TextInput::make('shipping_address.email')->disabled()->dehydrated(false)->label('Email'),
                    Forms\Components\TextInput::make('shipping_address.phone')->disabled()->dehydrated(false)->label('Phone'),
                    Forms\Components\TextInput::make('shipping_address.line1')->disabled()->dehydrated(false)->label('Address')->columnSpanFull(),
                    Forms\Components\TextInput::make('shipping_address.city')->disabled()->dehydrated(false)->label('City'),
                    Forms\Components\TextInput::make('shipping_address.zip')->disabled()->dehydrated(false)->label('Postcode'),
                ])->columns(2),

            Section::make('Order Items')
                ->schema([
                    Forms\Components\Repeater::make('items_display')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('product_name')->disabled()->dehydrated(false)->label('Product'),
                            Forms\Components\TextInput::make('product_sku')->disabled()->dehydrated(false)->label('SKU'),
                            Forms\Components\TextInput::make('quantity')->disabled()->dehydrated(false),
                            Forms\Components\TextInput::make('unit_price')->disabled()->dehydrated(false)->label('Unit Price')->prefix('€'),
                            Forms\Components\TextInput::make('subtotal')->disabled()->dehydrated(false)->prefix('€'),
                            Forms\Components\TextInput::make('booking_details')->disabled()->dehydrated(false)->label('Booking Details')->columnSpanFull(),
                        ])
                        ->columns(5)
                        ->afterStateHydrated(function ($component, $record) {
                            if (! $record) {
                                return;
                            }
                            $component->state(
                                $record->items->map(fn ($item) => [
                                    'product_name' => $item->product_name,
                                    'product_sku' => $item->product_sku ?: '—',
                                    'quantity' => (string) $item->quantity,
                                    'unit_price' => number_format((float) $item->unit_price, 2),
                                    'subtotal' => number_format((float) $item->subtotal, 2),
                                    'booking_details' => self::formatItemOptions($item->options ?? []),
                                ])->toArray()
                            );
                        })
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->dehydrated(false),
                ]),
        ]);
    }

    private static function formatItemOptions(array $options): string
    {
        $labels = [
            'booking_date' => 'Date',
            'pickup_date' => 'Pickup',
            'return_date' => 'Return',
            'check_in' => 'Check-in',
            'check_out' => 'Check-out',
            'guests' => 'Guests',
            'pickup_location_name' => 'Pickup location',
            'dropoff_location_name' => 'Drop-off location',
            'pickup_fee' => 'Pickup fee',
            'dropoff_fee' => 'Drop-off fee',
        ];

        $parts = [];
        foreach ($labels as $key => $label) {
            if (! empty($options[$key])) {
                $parts[] = $label.': '.$options[$key];
            }
        }

        if (! empty($options['extras'])) {
            $parts[] = 'Extras: '.implode(', ', (array) $options['extras']);
        }

        return implode(' · ', $parts) ?: '—';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->billing_address['name'] ?? $record->user?->name ?? 'Guest')
                    ->description(fn ($record) => $record->billing_address['email'] ?? $record->user?->email)
                    ->searchable(query: fn ($query, $search) => $query->where(fn ($q) => $q
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.name')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(billing_address, '$.email')) LIKE ?", ["%{$search}%"])
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    )),
                Tables\Columns\TextColumn::make('billing_city')
                    ->label('City')
                    ->getStateUsing(fn ($record) => $record->billing_address['city'] ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('gray'),
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
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'paid' => 'Paid',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded']),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'stripe' => 'Stripe']),
                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
                    ),
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
