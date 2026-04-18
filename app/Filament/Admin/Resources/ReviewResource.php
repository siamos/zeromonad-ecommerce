<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-star'; }
    protected static ?int $navigationSort = 55;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('product_id')->relationship('product', 'name')->required()->searchable(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->required()->searchable(),
            Forms\Components\Select::make('rating')->options([1=>1, 2=>2, 3=>3, 4=>4, 5=>5])->required(),
            Forms\Components\Textarea::make('body')->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('approved')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rating')->sortable(),
                Tables\Columns\TextColumn::make('body')->limit(60)->toggleable(),
                Tables\Columns\IconColumn::make('approved')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('approved'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Review $record) => !$record->approved)
                    ->action(fn (Review $record) => $record->update(['approved' => true])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
        ];
    }
}
