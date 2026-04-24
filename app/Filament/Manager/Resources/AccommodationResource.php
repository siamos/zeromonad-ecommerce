<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\AccommodationResource\Pages;
use App\Models\Accommodation;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-home';
    }

    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Bookings';
    }

    public static function canAccess(): bool
    {
        return app(GeneralSettings::class)->active_theme === 'Bookings';
    }

    public static function form(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create';

        return $schema->schema([
            Section::make('Basic Info')->schema([
                Tabs::make('Title')->tabs([
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('title.en')
                            ->label('Title (EN)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', str($state)->slug()))
                            ->visible($isCreate),
                    ]),
                    Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                        Forms\Components\TextInput::make('title.el')->label('Title (EL)'),
                    ]),
                ])->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->dehydrated($isCreate),
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where(fn ($q) => $q->where('theme', 'Bookings')->orWhereNull('theme'))
                    )
                    ->searchable()->preload(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default('draft')->required(),
                Forms\Components\TextInput::make('price_per_night')->numeric()->prefix('€')->required()->label('Price per Night'),
                Forms\Components\TextInput::make('compare_price')->numeric()->prefix('€'),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Section::make('Property Details')->schema([
                Forms\Components\TextInput::make('location'),
                Forms\Components\TextInput::make('bedrooms')->numeric()->default(1)->minValue(1),
                Forms\Components\TextInput::make('bathrooms')->numeric()->default(1)->minValue(1),
                Forms\Components\TextInput::make('max_guests')->numeric()->default(2)->minValue(1)->label('Max Guests'),
            ])->columns(2),

            Section::make('Amenities & Rules')->schema([
                Forms\Components\TagsInput::make('amenities')
                    ->suggestions(['WiFi', 'Pool', 'Parking', 'Air Conditioning', 'Kitchen', 'Balcony', 'Sea View', 'Gym', 'Jacuzzi', 'BBQ', 'Washing Machine', 'Dishwasher'])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('house_rules')->rows(4)->columnSpanFull(),
            ]),

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
                    ->collection('accommodation-images')
                    ->multiple()
                    ->maxFiles(15)
                    ->reorderable()
                    ->columnSpanFull(),
            ]),

            Section::make('Blocked Dates')->schema([
                Forms\Components\Repeater::make('blockedDates')
                    ->relationship()
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')->required()->native(false),
                        Forms\Components\DatePicker::make('end_date')->required()->native(false)->after('start_date'),
                        Forms\Components\TextInput::make('reason')->label('Reason (optional)'),
                    ])
                    ->columns(3)
                    ->addActionLabel('Block Date Range')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->collection('accommodation-images')
                    ->conversion('thumb')
                    ->label(''),
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(fn ($record) => $record->getTranslation('title', 'en'))
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.el')) LIKE ?", ["%{$search}%"])
                    )
                    ->limit(40),
                Tables\Columns\TextColumn::make('price_per_night')->money('EUR')->sortable()->label('Per Night'),
                Tables\Columns\TextColumn::make('bedrooms')->suffix(' bed')->toggleable(),
                Tables\Columns\TextColumn::make('max_guests')->suffix(' guests')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => null,
                    }),
                Tables\Columns\IconColumn::make('featured')->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\Filter::make('price_range')
                    ->label('Price / Night')
                    ->form([
                        Forms\Components\TextInput::make('min_price')->label('Min €')->numeric(),
                        Forms\Components\TextInput::make('max_price')->label('Max €')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['min_price'] ?? null, fn ($q, $v) => $q->where('price_per_night', '>=', $v))
                        ->when($data['max_price'] ?? null, fn ($q, $v) => $q->where('price_per_night', '<=', $v))
                    ),
                Tables\Filters\Filter::make('capacity')
                    ->label('Capacity')
                    ->form([
                        Forms\Components\TextInput::make('min_bedrooms')->label('Min Bedrooms')->numeric(),
                        Forms\Components\TextInput::make('min_guests')->label('Min Guests')->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['min_bedrooms'] ?? null, fn ($q, $v) => $q->where('bedrooms', '>=', $v))
                        ->when($data['min_guests'] ?? null, fn ($q, $v) => $q->where('max_guests', '>=', $v))
                    ),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccommodations::route('/'),
            'create' => Pages\CreateAccommodation::route('/create'),
            'edit' => Pages\EditAccommodation::route('/{record}/edit'),
        ];
    }
}
