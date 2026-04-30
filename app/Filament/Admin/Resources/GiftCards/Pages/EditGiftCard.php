<?php

namespace App\Filament\Admin\Resources\GiftCards\Pages;

use App\Filament\Admin\Resources\GiftCards\GiftCardResource;
use App\Mail\GiftCardIssued;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditGiftCard extends EditRecord
{
    protected static string $resource = GiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendToCustomer')
                ->label('Send to Customer')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->schema([
                    TextInput::make('recipient_email')
                        ->label('Recipient Email')
                        ->email()
                        ->required(),
                    TextInput::make('recipient_name')
                        ->label('Recipient Name')
                        ->nullable(),
                    Textarea::make('personal_message')
                        ->label('Personal Message (optional)')
                        ->rows(3)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    Mail::send(new GiftCardIssued(
                        giftCard: $this->record,
                        recipientEmail: $data['recipient_email'],
                        recipientName: $data['recipient_name'] ?: null,
                        personalMessage: $data['personal_message'] ?: null,
                    ));

                    Notification::make()
                        ->title('Gift card sent to '.$data['recipient_email'])
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
