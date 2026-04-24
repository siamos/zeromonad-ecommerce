<?php

namespace App\Filament\Manager\Pages;

use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
        $paletteKey = strtolower($settings->active_theme).'_palette';

        $this->form->fill([
            'site_logo' => $settings->site_logo ? [$settings->site_logo] : [],
            'hero_image' => $settings->hero_image ? [$settings->hero_image] : [],
            'hero_title' => $settings->hero_title,
            'hero_subtitle' => $settings->hero_subtitle,
            'active_palette' => $settings->{$paletteKey},
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

                Section::make('Theme Color Palette')
                    ->description('Choose the color palette for the active theme.')
                    ->schema([
                        Select::make('active_palette')
                            ->label('Color Palette')
                            ->options(fn () => match (app(GeneralSettings::class)->active_theme) {
                                'Activities' => [
                                    'emerald' => 'Emerald', 'teal' => 'Teal', 'cyan' => 'Cyan', 'green' => 'Green',
                                    'lime' => 'Lime', 'sky' => 'Sky', 'blue' => 'Blue', 'purple' => 'Purple',
                                    'amber' => 'Amber', 'orange' => 'Orange',
                                ],
                                'Bookings' => [
                                    'amber' => 'Amber', 'orange' => 'Orange', 'rose' => 'Rose', 'pink' => 'Pink',
                                    'red' => 'Red', 'fuchsia' => 'Fuchsia', 'purple' => 'Purple', 'violet' => 'Violet',
                                    'yellow' => 'Yellow', 'indigo' => 'Indigo',
                                ],
                                'Cars' => [
                                    'slate' => 'Slate', 'zinc' => 'Zinc', 'stone' => 'Stone', 'neutral' => 'Neutral',
                                    'gray' => 'Gray', 'blue' => 'Blue', 'indigo' => 'Indigo', 'teal' => 'Teal',
                                    'sky' => 'Sky', 'emerald' => 'Emerald',
                                ],
                                default => [
                                    'indigo' => 'Indigo', 'violet' => 'Violet', 'blue' => 'Blue', 'purple' => 'Purple',
                                    'emerald' => 'Emerald', 'teal' => 'Teal', 'cyan' => 'Cyan', 'sky' => 'Sky',
                                    'rose' => 'Rose', 'pink' => 'Pink', 'fuchsia' => 'Fuchsia', 'orange' => 'Orange',
                                    'amber' => 'Amber', 'lime' => 'Lime', 'green' => 'Green',
                                ],
                            })
                            ->required(),
                    ]),

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

        $paletteKey = strtolower($settings->active_theme).'_palette';
        $settings->{$paletteKey} = $data['active_palette'];

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
