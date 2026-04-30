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
use Filament\Schemas\Components\Utilities\Set;
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

    public static function shouldRegisterNavigation(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Products';
    }

    public static function canAccess(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Products';
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create';

        return $schema->schema([
            Section::make('Product Details')->schema([
                Forms\Components\TextInput::make('name.en')
                    ->label('Name (EN)')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', str($state)->slug()))
                    ->visible($isCreate),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->visible($isCreate),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible($isCreate),
                Forms\Components\TextInput::make('sku')
                    ->disabled(! $isCreate)
                    ->dehydrated($isCreate),
                Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(),
                Forms\Components\TextInput::make('stock')->numeric()->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default('draft')
                    ->required(),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Section::make('Volume Pricing')
                ->description('Optional tiered prices applied at checkout based on quantity.')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('priceTiers')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('min_quantity')->numeric()->minValue(2)->required()->label('Min Qty'),
                            Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required()->label('Price per Unit'),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add Price Tier')
                        ->orderColumn(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Flash Sale')
                ->description('Leave blank to disable the sale pricing.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('sale_price')->numeric()->prefix('€'),
                    Forms\Components\DateTimePicker::make('sale_starts_at')->label('Sale Starts At')->native(false),
                    Forms\Components\DateTimePicker::make('sale_ends_at')->label('Sale Ends At')->native(false),
                ])->columns(3),

            Section::make('Translations')->schema([
                Tabs::make('Language')->tabs([
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('name.en')
                            ->label('Name (EN)')
                            ->disabled()
                            ->visible(! $isCreate),
                        Forms\Components\TextInput::make('short_description.en')
                            ->label('Short Description (EN)')
                            ->disabled(! $isCreate)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description.en')
                            ->label('Description (EN)')
                            ->disabled(! $isCreate)
                            ->columnSpanFull(),
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
                        ->orWhere('sku', 'like', "%{$search}%")
                    )
                    ->limit(40),
                Tables\Columns\TextColumn::make('sku')->toggleable()->copyable(),
                Tables\Columns\TextColumn::make('category.name')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable()
                    ->color(fn ($state) => $state <= app(GeneralSettings::class)->low_stock_threshold ? 'danger' : null),
                Tables\Columns\IconColumn::make('featured')->boolean()->toggleable(),
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\Filter::make('price_range')
                    ->label('Price Range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')->label('Min €')->numeric(),
                        Forms\Components\TextInput::make('max_price')->label('Max €')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['min_price'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
                        ->when($data['max_price'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
                    ),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low / Out of Stock')
                    ->query(fn ($query) => $query->where('stock', '<=', app(GeneralSettings::class)->low_stock_threshold)),
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
