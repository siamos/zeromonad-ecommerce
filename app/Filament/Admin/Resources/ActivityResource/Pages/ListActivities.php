<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\Activity;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCsv')
                ->label('Import CSV')
                ->icon(Heroicon::ArrowUpTray)
                ->color('gray')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->helperText('Columns: title, price, location, duration_minutes, max_participants, status, featured, short_description, description')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/octet-stream'])
                        ->disk('local')
                        ->directory('csv-imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['csv_file']);
                    $handle = fopen($path, 'r');

                    $headers = array_map(fn ($h) => strtolower(trim($h)), fgetcsv($handle));

                    $created = 0;
                    $updated = 0;

                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) !== count($headers)) {
                            continue;
                        }

                        $record = array_combine($headers, $row);
                        $title = trim($record['title'] ?? '');

                        if (empty($title)) {
                            continue;
                        }

                        $existing = Activity::whereJsonContains('title->en', $title)->first();

                        $attributes = [
                            'title'             => ['en' => $title],
                            'price'             => (float) ($record['price'] ?? 0),
                            'location'          => trim($record['location'] ?? '') ?: null,
                            'duration_minutes'  => filled($record['duration_minutes'] ?? '') ? (int) $record['duration_minutes'] : null,
                            'max_participants'  => filled($record['max_participants'] ?? '') ? (int) $record['max_participants'] : null,
                            'status'            => in_array($record['status'] ?? 'active', ['active', 'inactive', 'draft']) ? $record['status'] : 'active',
                            'featured'          => in_array(strtolower($record['featured'] ?? ''), ['true', '1', 'yes']),
                            'short_description' => filled($record['short_description'] ?? '') ? ['en' => $record['short_description']] : null,
                            'description'       => filled($record['description'] ?? '') ? ['en' => $record['description']] : null,
                        ];

                        if ($existing) {
                            $existing->update($attributes);
                            $updated++;
                        } else {
                            Activity::create($attributes);
                            $created++;
                        }
                    }

                    fclose($handle);
                    Storage::disk('local')->delete($data['csv_file']);

                    Notification::make()
                        ->title("Import complete: {$created} created, {$updated} updated")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
