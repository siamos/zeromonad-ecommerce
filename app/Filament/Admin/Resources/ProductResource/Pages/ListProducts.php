<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

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
                        ->helperText('Columns: name, sku, price, compare_price, stock, status, featured, short_description, description')
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
                        $name = trim($record['name'] ?? '');

                        if (empty($name)) {
                            continue;
                        }

                        $sku = trim($record['sku'] ?? '');
                        $product = $sku ? Product::where('sku', $sku)->first() : null;

                        $attributes = [
                            'name'              => ['en' => $name],
                            'price'             => (float) ($record['price'] ?? 0),
                            'compare_price'     => filled($record['compare_price'] ?? '') ? (float) $record['compare_price'] : null,
                            'stock'             => (int) ($record['stock'] ?? 0),
                            'status'            => in_array($record['status'] ?? 'active', ['active', 'inactive', 'draft']) ? $record['status'] : 'active',
                            'featured'          => in_array(strtolower($record['featured'] ?? ''), ['true', '1', 'yes']),
                            'short_description' => filled($record['short_description'] ?? '') ? ['en' => $record['short_description']] : null,
                            'description'       => filled($record['description'] ?? '') ? ['en' => $record['description']] : null,
                        ];

                        if ($sku) {
                            $attributes['sku'] = $sku;
                        }

                        if ($product) {
                            $product->update($attributes);
                            $updated++;
                        } else {
                            Product::create($attributes);
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
