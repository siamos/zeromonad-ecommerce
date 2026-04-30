<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\PaymentGateways\PaymentGatewayManager;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->schema([
                    Select::make('status')
                        ->label('New Status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'paid' => 'Paid',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'refunded' => 'Refunded',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $newStatus = $data['status'];

                    if ($newStatus === $this->record->status) {
                        Notification::make()->title('Status unchanged')->warning()->send();

                        return;
                    }

                    $this->record->update(['status' => $newStatus]);

                    Notification::make()
                        ->title('Status updated to '.ucfirst(str_replace('_', ' ', $newStatus)))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Action::make('refund')
                ->label('Issue Refund')
                ->icon('heroicon-o-banknotes')
                ->color('danger')
                ->visible(fn () => $this->record->payment_status === 'paid')
                ->requiresConfirmation()
                ->modalHeading('Issue Refund')
                ->modalDescription('This will call the payment gateway to process the refund and mark the order as refunded.')
                ->schema([
                    TextInput::make('amount')
                        ->label('Refund Amount (€)')
                        ->numeric()
                        ->minValue(0.01)
                        ->default(fn () => (float) $this->record->total)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $order = $this->record;
                    $amount = (float) $data['amount'];

                    $gateway = app(PaymentGatewayManager::class)->resolve($order->payment_method);
                    $success = $gateway->refund($order, $amount);

                    if (! $success) {
                        Notification::make()->title('Refund failed — check gateway logs')->danger()->send();

                        return;
                    }

                    $order->update([
                        'payment_status' => 'refunded',
                        'status' => 'refunded',
                    ]);

                    Notification::make()
                        ->title('Refund of €'.number_format($amount, 2).' issued successfully')
                        ->success()
                        ->send();
                }),

            Action::make('downloadInvoice')
                ->label('Download Invoice')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('account.orders.invoice', $this->record->id))
                ->openUrlInNewTab(),

            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Order Overview')
                ->columns(4)
                ->schema([
                    TextEntry::make('order_number')
                        ->label('Order #')
                        ->copyable()
                        ->weight('bold'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn ($state) => match (true) {
                            $state === 'pending' => 'warning',
                            $state === 'processing' => 'primary',
                            in_array($state, ['paid', 'delivered', 'shipped']) => 'success',
                            in_array($state, ['cancelled', 'refunded']) => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('payment_status')
                        ->label('Payment')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'paid' => 'success', 'unpaid' => 'warning', 'refunded' => 'gray', default => null,
                        }),
                    TextEntry::make('payment_method')
                        ->label('Method')
                        ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                    TextEntry::make('created_at')
                        ->label('Placed')
                        ->dateTime('d M Y, H:i')
                        ->columnSpan(2),
                    TextEntry::make('user.name')
                        ->label('Customer')
                        ->default('Guest'),
                    TextEntry::make('user.email')
                        ->label('Email')
                        ->default(fn ($record) => $record->billing_address['email'] ?? '—'),
                ]),

            Section::make('Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('product_name')->label('Product')->weight('medium'),
                            TextEntry::make('product_sku')->label('SKU')->default('—'),
                            TextEntry::make('unit_price')->label('Unit Price')->money('EUR'),
                            TextEntry::make('quantity')->label('Qty'),
                            TextEntry::make('subtotal')->label('Subtotal')->money('EUR')->weight('bold'),
                        ])
                        ->columns(5),
                ]),

            Section::make('Financial Summary')
                ->columns(4)
                ->schema([
                    TextEntry::make('subtotal')->money('EUR'),
                    TextEntry::make('discount_amount')->label('Discount')->money('EUR'),
                    TextEntry::make('tax_amount')->label('Tax')->money('EUR'),
                    TextEntry::make('total')->money('EUR')->weight('bold')->size('lg'),
                ]),

            Grid::make(2)->schema([
                Section::make('Billing Address')
                    ->schema([
                        TextEntry::make('billing_address.name')->label('Name')->default('—'),
                        TextEntry::make('billing_address.email')->label('Email')->default('—'),
                        TextEntry::make('billing_address.phone')->label('Phone')->default('—'),
                        TextEntry::make('billing_address.line1')->label('Address')->default('—'),
                        TextEntry::make('billing_address.city')->label('City')->default('—'),
                        TextEntry::make('billing_address.zip')->label('ZIP')->default('—'),
                        TextEntry::make('billing_address.country')->label('Country')->default('—'),
                    ])
                    ->columns(2),

                Section::make('Shipping Address')
                    ->schema([
                        TextEntry::make('shipping_address.name')->label('Name')->default('—'),
                        TextEntry::make('shipping_address.email')->label('Email')->default('—'),
                        TextEntry::make('shipping_address.phone')->label('Phone')->default('—'),
                        TextEntry::make('shipping_address.line1')->label('Address')->default('—'),
                        TextEntry::make('shipping_address.city')->label('City')->default('—'),
                        TextEntry::make('shipping_address.zip')->label('ZIP')->default('—'),
                        TextEntry::make('shipping_address.country')->label('Country')->default('—'),
                    ])
                    ->columns(2),
            ]),

            Section::make('Notes')
                ->collapsed()
                ->schema([
                    TextEntry::make('notes')->label('')->default('No notes.'),
                ]),

        ]);
    }
}
