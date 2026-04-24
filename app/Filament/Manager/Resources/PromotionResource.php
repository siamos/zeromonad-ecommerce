<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-megaphone';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        $activeTheme = app(GeneralSettings::class)->active_theme;

        return $schema->schema([
            Section::make('Content')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\TextInput::make('subtitle')->maxLength(255),
                Forms\Components\TextInput::make('cta_label')->required()->label('Button Label')->maxLength(100),
                Forms\Components\TextInput::make('cta_url')->required()->label('Button URL')->url()->maxLength(500),
                Forms\Components\TextInput::make('coupon_code')->maxLength(50)->label('Coupon Code (optional)'),
            ])->columns(2),

            Section::make('Background')->schema([
                Forms\Components\Select::make('background_type')
                    ->options(['color' => 'Solid Color', 'image' => 'Background Image'])
                    ->default('color')
                    ->required()
                    ->live(),
                Forms\Components\ColorPicker::make('background_color')
                    ->default('#1e3a5f')
                    ->visible(fn (Get $get) => $get('background_type') === 'color'),
                Forms\Components\SpatieMediaLibraryFileUpload::make('background_image')
                    ->collection('promotion-background')
                    ->visibility('public')
                    ->image()
                    ->visible(fn (Get $get) => $get('background_type') === 'image')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Scheduling')->schema([
                Forms\Components\Hidden::make('theme')->default($activeTheme),
                Forms\Components\TextInput::make('delay_seconds')
                    ->numeric()
                    ->default(3)
                    ->suffix('seconds')
                    ->label('Show Delay'),
                Forms\Components\DateTimePicker::make('starts_at')->label('Active From'),
                Forms\Components\DateTimePicker::make('ends_at')->label('Active Until'),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('inactive')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('background_type')->badge()->label('Background'),
                Tables\Columns\TextColumn::make('coupon_code')->badge()->label('Coupon'),
                Tables\Columns\TextColumn::make('delay_seconds')->suffix('s')->label('Delay'),
                Tables\Columns\TextColumn::make('starts_at')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
