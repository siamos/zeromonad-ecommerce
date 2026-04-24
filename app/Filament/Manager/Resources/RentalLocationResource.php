<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\RentalLocationResource\Pages;
use App\Models\RentalLocation;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RentalLocationResource extends Resource
{
    protected static ?string $model = RentalLocation::class;

    protected static ?string $navigationLabel = 'Rental Locations';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-map-pin';
    }

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Cars';
    }

    public static function canAccess(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Cars';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Location Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('address')
                    ->maxLength(255)
                    ->placeholder('e.g. Athens International Airport, Terminal 1'),

                Forms\Components\Select::make('type')
                    ->options(['branch' => 'Branch / Office', 'delivery' => 'Delivery / Collection'])
                    ->required()
                    ->default('branch')
                    ->helperText('Branch: customer comes to you. Delivery: you go to the customer.'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Display Order'),
            ])->columns(2),

            Section::make('Availability & Fees')->schema([
                Forms\Components\Toggle::make('pickup_available')
                    ->label('Available for Pickup')
                    ->default(true)
                    ->inline(false),

                Forms\Components\TextInput::make('pickup_fee')
                    ->numeric()
                    ->prefix('€')
                    ->default(0)
                    ->label('Pickup Fee')
                    ->helperText('0 = free'),

                Forms\Components\Toggle::make('dropoff_available')
                    ->label('Available for Drop-off')
                    ->default(true)
                    ->inline(false),

                Forms\Components\TextInput::make('dropoff_fee')
                    ->numeric()
                    ->prefix('€')
                    ->default(0)
                    ->label('Drop-off Fee')
                    ->helperText('0 = free'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('address')->limit(40)->placeholder('—'),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors(['primary' => 'branch', 'warning' => 'delivery']),
                Tables\Columns\IconColumn::make('pickup_available')->boolean()->label('Pickup'),
                Tables\Columns\IconColumn::make('dropoff_available')->boolean()->label('Drop-off'),
                Tables\Columns\TextColumn::make('pickup_fee')->money('EUR')->label('Pickup Fee'),
                Tables\Columns\TextColumn::make('dropoff_fee')->money('EUR')->label('Drop-off Fee'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Order'),
            ])
            ->defaultSort('sort_order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentalLocations::route('/'),
            'create' => Pages\CreateRentalLocation::route('/create'),
            'edit' => Pages\EditRentalLocation::route('/{record}/edit'),
        ];
    }
}
