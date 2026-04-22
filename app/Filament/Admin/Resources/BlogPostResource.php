<?php

namespace App\Filament\Admin\Resources;

use App\Actions\Blog\GenerateBlogPost;
use App\Filament\Admin\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-document-text';
    }

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')->required(),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\Toggle::make('ai_generated')->disabled(),
            ])->columns(2),

            Tabs::make('Translations')->tabs([
                Tabs\Tab::make('English')->schema([
                    Forms\Components\TextInput::make('title.en')
                        ->label('Title (EN)')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', str($state)->slug())),
                    Forms\Components\TextInput::make('featured_image')->url()->label('Featured Image URL'),
                    Forms\Components\Textarea::make('excerpt.en')->label('Excerpt (EN)')->rows(2),
                    Forms\Components\RichEditor::make('content.en')->label('Content (EN)')->columnSpanFull(),
                ])->columns(2),

                Tabs\Tab::make('Greek (Ελληνικά)')->schema([
                    Forms\Components\TextInput::make('title.el')->label('Title (EL)'),
                    Forms\Components\Textarea::make('excerpt.el')->label('Excerpt (EL)')->rows(2),
                    Forms\Components\RichEditor::make('content.el')->label('Content (EL)')->columnSpanFull(),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(fn ($record) => $record->getTranslation('title', 'en'))
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.el')) LIKE ?", ["%{$search}%"])
                    )
                    ->sortable(query: fn ($query, $direction) => $query
                        ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) {$direction}")
                    )
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        default => null,
                    }),
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
                Action::make('generate')
                    ->label('Generate AI Post')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('topic')->placeholder('Leave blank for random topic'),
                    ])
                    ->action(function (array $data) {
                        GenerateBlogPost::run($data['topic'] ?: null);
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
