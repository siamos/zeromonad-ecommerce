<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityResource\Pages;
use App\Models\Activity;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Activities';
    }

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic Info')->schema([
                Tabs::make('Title')->tabs([
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('title.en')
                            ->label('Title (EN)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', str($state)->slug())),
                    ]),
                    Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                        Forms\Components\TextInput::make('title.el')->label('Title (EL)'),
                    ]),
                ])->columnSpanFull(),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where(fn ($q) => $q->where('theme', 'Activities')->orWhereNull('theme'))
                    )
                    ->searchable()->preload(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default('draft')->required(),
                Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(),
                Forms\Components\TextInput::make('compare_price')->numeric()->prefix('€'),
                Forms\Components\Toggle::make('featured'),
            ])->columns(2),

            Section::make('Details')->schema([
                Forms\Components\TextInput::make('location'),
                Forms\Components\TextInput::make('duration_minutes')->numeric()->suffix('min')->label('Duration'),
                Forms\Components\TextInput::make('max_participants')->numeric()->label('Max Participants'),
                Forms\Components\TextInput::make('booking_cutoff_hours')->numeric()->suffix('h before')->label('Booking Cutoff')->default(24),
                Forms\Components\Select::make('difficulty')
                    ->options(['easy' => 'Easy', 'moderate' => 'Moderate', 'hard' => 'Hard', 'expert' => 'Expert'])
                    ->nullable(),
                Forms\Components\TextInput::make('min_age')->numeric()->minValue(1)->label('Minimum Age')->suffix('years'),
                Forms\Components\Toggle::make('weather_dependent')->label('Weather Dependent')->helperText('Warn customers this activity may be cancelled due to weather'),
                Forms\Components\Textarea::make('cancellation_policy')->label('Cancellation Policy')->rows(3)->columnSpanFull(),
            ])->columns(2),

            Section::make('Group Booking')->schema([
                Forms\Components\TextInput::make('min_participants')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->label('Min Participants')
                    ->helperText('Minimum group size required to book'),
                Forms\Components\TextInput::make('price_per_person')
                    ->numeric()
                    ->prefix('€')
                    ->label('Price Per Person')
                    ->helperText('When set, overrides base price for group calculations'),
                Forms\Components\Repeater::make('age_pricing')
                    ->label('Age / Group Pricing Tiers')
                    ->helperText('Optional. When set, the booking form shows a counter per tier instead of a single participants counter.')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Label')
                            ->placeholder('e.g. Adult, Child, Senior')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Tier')
                    ->orderColumn(false)
                    ->columnSpanFull()
                    ->defaultItems(0),
            ])->columns(2),

            Section::make('Descriptions')->schema([
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
                                ->placeholder('e.g. great for families, moderate difficulty, scenic mountain views'),
                        ])
                        ->action(function (array $data, callable $schemaGet, callable $schemaSet): void {
                            $name = $schemaGet('title.en');

                            if (blank($name)) {
                                Notification::make()->title('Please fill in the activity title first.')->warning()->send();

                                return;
                            }

                            try {
                                $result = app(AiDescriptionService::class)->generate($name, 'activity', $data['provider'], $data['hint'] ?? null);
                                $schemaSet('short_description.en', $result['short']);
                                $schemaSet('description.en', $result['long']);
                                Notification::make()->title('Description generated successfully.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Generation failed: '.$e->getMessage())->danger()->send();
                            }
                        }),
                ]),
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

            Section::make('Images')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->collection('activity-images')
                    ->multiple()
                    ->maxFiles(10)
                    ->reorderable()
                    ->columnSpanFull(),
            ]),

            Section::make('Date Slots')->schema([
                Forms\Components\Repeater::make('slots')
                    ->relationship()
                    ->schema([
                        Forms\Components\DatePicker::make('date')->required()->native(false),
                        Forms\Components\TextInput::make('capacity')->numeric()->minValue(1)->required(),
                        Forms\Components\TextInput::make('booked_count')->numeric()->default(0)->disabled()->dehydrated()->label('Booked'),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add Date Slot')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->collection('activity-images')
                    ->conversion('thumb')
                    ->label(''),
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(fn ($record) => $record->getTranslation('title', 'en'))
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.el')) LIKE ?", ["%{$search}%"])
                    )
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('location')->limit(25)->toggleable(),
                Tables\Columns\TextColumn::make('duration_minutes')->suffix(' min')->label('Duration')->sortable()->toggleable(),
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
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
