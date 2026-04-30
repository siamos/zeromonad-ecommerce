<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-truck';
    }

    protected static ?int $navigationSort = 10;

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
            Section::make('Vehicle Info')->schema([
                Forms\Components\TextInput::make('make')->required(),
                Forms\Components\TextInput::make('model')->required(),
                Forms\Components\TextInput::make('year')->numeric()->required()->minValue(1900)->maxValue(2100),
                Forms\Components\Select::make('vehicle_type')
                    ->options(['car' => 'Car', 'suv' => 'SUV', 'van' => 'Van', 'motorcycle' => 'Motorcycle', 'truck' => 'Truck'])
                    ->required(),
                Forms\Components\Select::make('transmission')
                    ->options(['manual' => 'Manual', 'automatic' => 'Automatic'])
                    ->required(),
                Forms\Components\TextInput::make('seats')->numeric()->required()->minValue(1),
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where(fn ($q) => $q->where('theme', 'Cars')->orWhereNull('theme'))
                    )
                    ->searchable()->preload(),
            ])->columns(2),

            Section::make('Pricing & Availability')->schema([
                Forms\Components\TextInput::make('price_per_day')->numeric()->prefix('€')->required()->label('Price per Day'),
                Forms\Components\TextInput::make('compare_price')->numeric()->prefix('€'),
                Forms\Components\TextInput::make('pickup_location')->label('Pickup Location'),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default('draft')->required(),
                Forms\Components\Toggle::make('is_available')->default(true)->label('Available for Booking'),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Section::make('Policies')->schema([
                Forms\Components\Textarea::make('mileage_policy')->rows(2)->label('Mileage Policy'),
                Forms\Components\Textarea::make('fuel_policy')->rows(2)->label('Fuel Policy'),
            ])->columns(2),

            Section::make('Extras')->schema([
                Forms\Components\Repeater::make('extras')
                    ->schema([
                        Forms\Components\TextInput::make('key')->required()->label('Key'),
                        Forms\Components\TextInput::make('label')->required()->label('Label'),
                        Forms\Components\TextInput::make('price_per_day')->numeric()->prefix('€')->required()->label('Price/Day'),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add Extra')
                    ->columnSpanFull(),
            ]),

            Section::make('Volume Pricing')
                ->description('Optional tiered prices applied at checkout based on quantity.')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('priceTiers')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('min_quantity')->numeric()->minValue(2)->required()->label('Min Days'),
                            Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required()->label('Price per Day'),
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
                    Forms\Components\TextInput::make('sale_price')->numeric()->prefix('€')->label('Sale Price per Day'),
                    Forms\Components\DateTimePicker::make('sale_starts_at')->label('Sale Starts At')->native(false),
                    Forms\Components\DateTimePicker::make('sale_ends_at')->label('Sale Ends At')->native(false),
                ])->columns(3),

            Section::make('Descriptions')->schema([
                Tabs::make('Translations')->tabs([
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('short_description.en')->label('Short Description (EN)')->maxLength(500)->columnSpanFull(),
                        Forms\Components\RichEditor::make('description.en')->label('Description (EN)')->columnSpanFull(),
                    ]),
                    Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                        Forms\Components\TextInput::make('short_description.el')->label('Short Description (EL)')->maxLength(500)->columnSpanFull(),
                        Forms\Components\RichEditor::make('description.el')->label('Description (EL)')->columnSpanFull(),
                    ]),
                ])->columnSpanFull(),
            ]),

            Section::make('Images')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->collection('vehicle-images')
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
                    ->collection('vehicle-images')
                    ->conversion('thumb')
                    ->label(''),
                Tables\Columns\TextColumn::make('make')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('model')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('year')->sortable(),
                Tables\Columns\TextColumn::make('vehicle_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('price_per_day')->money('EUR')->sortable()->label('Per Day'),
                Tables\Columns\IconColumn::make('is_available')->boolean()->label('Available'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => null,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
                SelectFilter::make('vehicle_type')->options(['car' => 'Car', 'suv' => 'SUV', 'van' => 'Van', 'motorcycle' => 'Motorcycle', 'truck' => 'Truck']),
                SelectFilter::make('transmission')->options(['manual' => 'Manual', 'automatic' => 'Automatic']),
                Tables\Filters\TernaryFilter::make('is_available')->label('Available'),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\Filter::make('price_range')
                    ->label('Price / Day')
                    ->form([
                        Forms\Components\TextInput::make('min_price')->label('Min €')->numeric(),
                        Forms\Components\TextInput::make('max_price')->label('Max €')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['min_price'] ?? null, fn ($q, $v) => $q->where('price_per_day', '>=', $v))
                        ->when($data['max_price'] ?? null, fn ($q, $v) => $q->where('price_per_day', '<=', $v))
                    ),
                Tables\Filters\Filter::make('year_range')
                    ->label('Year Range')
                    ->form([
                        Forms\Components\TextInput::make('year_from')->label('From Year')->numeric(),
                        Forms\Components\TextInput::make('year_to')->label('To Year')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['year_from'] ?? null, fn ($q, $v) => $q->where('year', '>=', $v))
                        ->when($data['year_to'] ?? null, fn ($q, $v) => $q->where('year', '<=', $v))
                    ),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
