<?php

namespace App\Filament\Manager\Pages;

use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppearanceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-paint-brush';
    }

    public static function getNavigationLabel(): string
    {
        return 'Appearance';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected static ?int $navigationSort = 70;

    public function getView(): string
    {
        return 'filament.manager.pages.appearance-settings';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);

        $this->form->fill([
            'site_logo' => $settings->site_logo ? [$settings->site_logo] : [],
            'hero_image' => $settings->hero_image ? [$settings->hero_image] : [],
            'hero_title' => $settings->hero_title,
            'hero_subtitle' => $settings->hero_subtitle,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Branding')->schema([
                    FileUpload::make('site_logo')
                        ->label('Site Logo')
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
                        ->maxSize(2048)
                        ->helperText('Accepted: SVG, PNG, WebP · Max 2 MB. SVG recommended for sharpness at all sizes.')
                        ->imagePreviewHeight('80')
                        ->columnSpanFull(),
                ]),

                Section::make('Hero Section')
                    ->description('Customise the full-width banner shown on the home page. Leave blank to use the default text.')
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Headline')
                            ->maxLength(120)
                            ->placeholder('Discover & Book Your Next Experience')
                            ->columnSpanFull(),
                        Textarea::make('hero_subtitle')
                            ->label('Subtitle')
                            ->rows(2)
                            ->maxLength(300)
                            ->placeholder('Activities, accommodations, vehicles, tours, and events — all in one place.')
                            ->columnSpanFull(),
                        FileUpload::make('hero_image')
                            ->label('Hero Background Image')
                            ->disk('public')
                            ->directory('hero')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Accepted: JPEG, PNG, WebP · Max 5 MB · Recommended: 1920×1080 px or wider.')
                            ->imagePreviewHeight('160')
                            ->columnSpanFull(),
                    ])->columns(1),

                Actions::make($this->getFormActions()),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        $settings->site_logo = $data['site_logo'][0] ?? null;
        $settings->hero_image = $data['hero_image'][0] ?? null;
        $settings->hero_title = $data['hero_title'] ?: null;
        $settings->hero_subtitle = $data['hero_subtitle'] ?: null;
        $settings->save();

        Notification::make()->title('Appearance saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Appearance')
                ->action('save'),
        ];
    }
}
