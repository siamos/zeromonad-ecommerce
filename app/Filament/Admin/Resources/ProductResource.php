<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-cube'; }
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make('Product Details')->schema([
                Forms\Components\TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()->preload()->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default('draft')->required(),
                Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(),
                Forms\Components\TextInput::make('compare_price')->numeric()->prefix('€'),
                Forms\Components\TextInput::make('stock')->numeric()->default(0)->required(),
                Forms\Components\TextInput::make('sku')->unique(ignoreRecord: true),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Forms\Components\Section::make('Description')->schema([
                Forms\Components\TextInput::make('short_description')->maxLength(500),
                Forms\Components\RichEditor::make('description')->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Activity Details')
                ->description('Only needed when using the Activities theme')
                ->collapsed()
                ->schema([
                    Forms\Components\DateTimePicker::make('activity_detail.event_date'),
                    Forms\Components\TextInput::make('activity_detail.location'),
                    Forms\Components\TextInput::make('activity_detail.capacity')->numeric(),
                    Forms\Components\TextInput::make('activity_detail.duration_minutes')->numeric()->suffix('min'),
                    Forms\Components\TextInput::make('activity_detail.booking_cutoff_hours')->numeric()->suffix('h'),
                ])->columns(2),

            Forms\Components\Section::make('Images')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->collection('product-images')
                    ->multiple()
                    ->maxFiles(10)
                    ->reorderable()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->collection('product-images')
                    ->conversion('thumb')
                    ->label(''),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('category.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning'  => 'draft',
                        'success'  => 'published',
                        'gray'     => 'archived',
                    ]),
                Tables\Columns\IconColumn::make('featured')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ]),
                SelectFilter::make('category')->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
