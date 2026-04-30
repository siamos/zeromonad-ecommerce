<?php

namespace App\Filament\Manager\Resources\OrderResource\Pages;

use App\Filament\Manager\Resources\OrderResource;
use App\PaymentGateways\PaymentGatewayManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
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
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $newStatus = $data['status'];

                    if ($newStatus === $this->record->status) {
                        Notification::make()
                            ->title('Status unchanged')
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->record->update(['status' => $newStatus]);

                    Notification::make()
                        ->title('Status updated to '.ucfirst(str_replace('_', ' ', $newStatus)))
                        ->success()
                        ->send();
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
                        Notification::make()
                            ->title('Refund failed — check gateway logs')
                            ->danger()
                            ->send();

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

            DeleteAction::make(),
        ];
    }
}
