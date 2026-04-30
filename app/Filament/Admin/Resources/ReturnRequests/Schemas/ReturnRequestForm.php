<?php

namespace App\Filament\Admin\Resources\ReturnRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReturnRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order.order_number')
                            ->label('Order')
                            ->disabled(),
                        TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled(),
                        TextInput::make('reason')
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('details')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Decision')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'requested' => 'Requested',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        TextInput::make('refund_amount')
                            ->label('Refund Amount (€)')
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('notes')
                            ->label('Internal Notes (sent to customer)')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
