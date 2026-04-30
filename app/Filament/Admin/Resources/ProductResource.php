<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\AiDescriptionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cube';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Product Details')->schema([
                Tabs::make('Name')->tabs([
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('name.en')
                            ->label('Name (EN)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', str($state)->slug())),
                    ]),
                    Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                        Forms\Components\TextInput::make('name.el')->label('Name (EL)'),
                    ]),
                ])->columnSpanFull(),
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

            Section::make('Description')->schema([
                SchemaActions::make([
                    Action::make('generateDescription')
                        ->label('Generate with AI')
                        ->icon('heroicon-o-sparkles')
                        ->color('gray')
                        ->form(fn () => [
                            Forms\Components\Select::make('provider')
                                ->label('AI Provider')
                                ->options(fn () => app(AiDescriptionService::class)->availableProviders())
                                ->required()
                                ->default(fn () => array_key_first(app(AiDescriptionService::class)->availableProviders()) ?? 'openai'),
                            Forms\Components\Textarea::make('hint')
                                ->label('Describe what to emphasize (optional)')
                                ->rows(2)
                                ->placeholder('e.g. focus on eco-friendly materials, target audience is young adults'),
                        ])
                        ->action(function (array $data, callable $schemaGet, callable $schemaSet): void {
                            $name = $schemaGet('name.en');

                            if (blank($name)) {
                                Notification::make()->title('Please fill in the product name first.')->warning()->send();

                                return;
                            }

                            try {
                                $result = app(AiDescriptionService::class)->generate($name, 'product', $data['provider'], $data['hint'] ?? null);
                                $schemaSet('short_description.en', $result['short']);
                                $schemaSet('description.en', $result['long']);
                                Notification::make()->title('Description generated successfully.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Generation failed: '.$e->getMessage())->danger()->send();
                            }
                        }),
                ]),
                Tabs::make('Description Translations')->tabs([
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

            Section::make('Booking Details')
                ->description('Only needed when using the Booking Platform theme')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('activity_detail.booking_type')
                        ->label('Booking Type')
                        ->options([
                            'activity' => 'Activity',
                            'accommodation' => 'Accommodation',
                            'vehicle' => 'Vehicle',
                            'tour' => 'Tour',
                            'event' => 'Event',
                        ])
                        ->default('activity')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('activity_detail.location')
                        ->label('Location'),
                    Forms\Components\TextInput::make('activity_detail.capacity')
                        ->label('Capacity')
                        ->numeric(),
                    Forms\Components\DateTimePicker::make('activity_detail.event_date')
                        ->label('Event Date')
                        ->visible(fn (Get $get) => in_array($get('activity_detail.booking_type'), ['activity', 'tour', 'event', null])),
                    Forms\Components\TextInput::make('activity_detail.duration_minutes')
                        ->label('Duration')
                        ->numeric()
                        ->suffix('min')
                        ->visible(fn (Get $get) => in_array($get('activity_detail.booking_type'), ['activity', 'tour', 'event', null])),
                    Forms\Components\TextInput::make('activity_detail.booking_cutoff_hours')
                        ->label('Booking Cutoff')
                        ->numeric()
                        ->suffix('h before')
                        ->visible(fn (Get $get) => in_array($get('activity_detail.booking_type'), ['activity', 'tour', 'event', null])),
                    Forms\Components\KeyValue::make('activity_detail.extra_attributes')
                        ->label('Extra Attributes')
                        ->keyLabel('Attribute')
                        ->valueLabel('Value')
                        ->helperText('e.g. bedrooms / 3, bathrooms / 2 for accommodation; vehicle_type / SUV, transmission / automatic for vehicle')
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => in_array($get('activity_detail.booking_type'), ['accommodation', 'vehicle'])),
                    Forms\Components\Repeater::make('activitySlots')
                        ->label('Date Slots')
                        ->relationship()
                        ->schema([
                            Forms\Components\DatePicker::make('date')
                                ->label('Date')
                                ->required()
                                ->native(false),
                            Forms\Components\TextInput::make('capacity')
                                ->label('Capacity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            Forms\Components\TextInput::make('booked_count')
                                ->label('Booked')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(3)
                        ->columnSpanFull()
                        ->addActionLabel('Add Date Slot')
                        ->helperText('Add multiple date slots with individual capacities for this activity.')
                        ->visible(fn (Get $get) => in_array($get('activity_detail.booking_type'), ['activity', 'tour', 'event', null])),
                ])->columns(2),

            Section::make('Images')->schema([
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
                Tables\Columns\TextColumn::make('name')
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.el')) LIKE ?", ["%{$search}%"])
                    )
                    ->sortable(query: fn ($query, $direction) => $query
                        ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) {$direction}")
                    )
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => null,
                    }),
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
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
