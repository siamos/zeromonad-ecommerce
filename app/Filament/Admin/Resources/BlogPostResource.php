<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    public static function getNavigationIcon(): string|\BackedEnum|null { return 'heroicon-o-document-text'; }
    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')->required(),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\Toggle::make('ai_generated')->disabled(),
            ])->columns(2),

            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('featured_image')->url()->columnSpanFull(),
                Forms\Components\Textarea::make('excerpt')->rows(2)->columnSpanFull(),
                Forms\Components\RichEditor::make('content')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'draft', 'success' => 'published']),
                Tables\Columns\IconColumn::make('ai_generated')->boolean()->label('AI'),
                Tables\Columns\TextColumn::make('published_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published']),
                Tables\Filters\TernaryFilter::make('ai_generated')->label('AI Generated'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate')
                    ->label('Generate AI Post')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('topic')->placeholder('Leave blank for random topic'),
                    ])
                    ->action(function (array $data) {
                        \App\Actions\Blog\GenerateBlogPost::run($data['topic'] ?: null);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
