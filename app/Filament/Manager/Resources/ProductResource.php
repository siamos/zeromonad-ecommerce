<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cube';
    }

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Forms\Components\TextInput::make('sku')->disabled(),
                Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(),
                Forms\Components\TextInput::make('stock')->numeric()->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->required(),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Section::make('Translations')->schema([
                Tabs::make('Language')->tabs([
                    Tabs\Tab::make('English (Reference)')->schema([
                        Forms\Components\TextInput::make('name.en')->label('Name (EN)')->disabled(),
                        Forms\Components\TextInput::make('short_description.en')->label('Short Description (EN)')->disabled()->columnSpanFull(),
                        Forms\Components\Textarea::make('description.en')->label('Description (EN)')->disabled()->rows(4)->columnSpanFull(),
                    ])->columns(2),

                    Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                        Forms\Components\TextInput::make('name.el')->label('Name (EL)'),
                        Forms\Components\TextInput::make('short_description.el')->label('Short Description (EL)')->columnSpanFull(),
                        Forms\Components\Textarea::make('description.el')->label('Description (EL)')->rows(4)->columnSpanFull(),
                    ])->columns(2),
                ])->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.el')) LIKE ?", ["%{$search}%"])
                    )
                    ->limit(40),
                Tables\Columns\TextColumn::make('sku')->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable()
                    ->color(fn ($state) => $state <= app(GeneralSettings::class)->low_stock_threshold ? 'danger' : null),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => null,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
