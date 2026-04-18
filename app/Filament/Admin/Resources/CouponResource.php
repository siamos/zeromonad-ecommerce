<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-ticket'; }
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true)
                    ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('code', strtoupper($state))),
                Forms\Components\Select::make('type')
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'])
                    ->required()->reactive(),
                Forms\Components\TextInput::make('value')->numeric()->required()
                    ->suffix(fn (Forms\Get $get) => $get('type') === 'percentage' ? '%' : '€'),
                Forms\Components\TextInput::make('minimum_amount')->numeric()->prefix('€'),
                Forms\Components\TextInput::make('max_uses')->numeric(),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('value'),
                Tables\Columns\TextColumn::make('uses_count')->label('Used')->sortable(),
                Tables\Columns\TextColumn::make('max_uses')->label('Max'),
                Tables\Columns\TextColumn::make('expires_at')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
