<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-cube'; }
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required()->disabled(),
                Forms\Components\TextInput::make('sku')->disabled(),
                Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(),
                Forms\Components\TextInput::make('stock')->numeric()->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->required(),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('sku')->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable()
                    ->color(fn ($record) => $record->stock <= app(\App\Settings\GeneralSettings::class)->low_stock_threshold ? 'danger' : null),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'draft', 'success' => 'published', 'gray' => 'archived']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit'  => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
