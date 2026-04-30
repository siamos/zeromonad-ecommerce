<?php

namespace App\Filament\Admin\Resources\Bundles\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bundle Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('€')
                                ->minValue(0),
                            Select::make('theme')
                                ->options([
                                    'Products' => 'Products',
                                    'Activities' => 'Activities',
                                    'Bookings' => 'Bookings',
                                    'Cars' => 'Cars',
                                ])
                                ->placeholder('All themes')
                                ->native(false),
                            Toggle::make('is_active')
                                ->default(true)
                                ->inline(false),
                        ]),
                    ]),
                Section::make('Bundle Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Product')
                            ->orderColumn(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
