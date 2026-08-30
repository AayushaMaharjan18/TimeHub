<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $record->update($data);
        } catch (InvalidOrderStatusTransitionException $e) {
            Notification::make()
                ->title('Status change blocked')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }

        return $record;
    }
}
