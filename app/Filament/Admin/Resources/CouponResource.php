<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-ticket';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true)
                    ->afterStateUpdated(fn (Set $set, $state) => $set('code', strtoupper($state))),
                Forms\Components\Select::make('theme')
                    ->options([
                        'Products' => 'Products',
                        'Activities' => 'Activities',
                        'Bookings' => 'Bookings',
                        'Cars' => 'Cars',
                    ])
                    ->placeholder('All Themes')
                    ->nullable()
                    ->helperText('Limit this coupon to a specific theme.'),
                Forms\Components\Select::make('type')
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'])
                    ->required()->live(),
                Forms\Components\TextInput::make('value')->numeric()->required()
                    ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : '€'),
                Forms\Components\TextInput::make('minimum_amount')->numeric()->prefix('€'),
                Forms\Components\TextInput::make('max_uses')->numeric(),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\TextInput::make('required_segment')
                    ->placeholder('e.g. vip, wholesale')
                    ->helperText('Only users tagged with this segment can use this coupon.')
                    ->columnSpanFull(),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
