<?php

namespace App\Filament\Admin\Resources\GiftCards\Schemas;

use App\Models\GiftCard;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GiftCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->default(fn () => GiftCard::generateCode())
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                TextInput::make('original_amount')
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->minValue(1),
                TextInput::make('remaining_balance')
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
                DateTimePicker::make('expires_at')
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
