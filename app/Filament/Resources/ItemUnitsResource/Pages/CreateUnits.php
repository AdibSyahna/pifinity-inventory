<?php

namespace App\Filament\Resources\ItemUnitsResource\Pages;

use App\Filament\Resources\ItemUnitsResource;
use App\Models\Item;
use App\Models\ItemUnits;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreateUnits extends CreateRecord
{
    protected static string $resource = ItemUnitsResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        $storageId = (int) ($data['storage_id'] ?? 0);
        $itemId = (int) ($data['item_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        $item = Item::with(['category', 'color', 'size'])->findOrFail($itemId);

        if ($quantity < 1) {
            Notification::make()
                ->title('Quantity must be at least 1.')
                ->danger()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        $lastNumber = (int) (ItemUnits::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('item_id', $itemId)
            ->max('number') ?? 0);

        $startNumber = $lastNumber + 1;

        if (($startNumber + $quantity - 1) > 65535) {
            Notification::make()
                ->title('Adding this many units would exceed the available number range (up to 65535).')
                ->danger()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        $firstUnit = null;

        for ($offset = 0; $offset < $quantity; $offset++) {
            $number = $startNumber + $offset;
            $code = (string) $item->category->code . $item->size->code . $item->color->code . str_pad($number, 4, '0', STR_PAD_LEFT);
            $unit = ItemUnits::create([
                'item_id' => $itemId,
                'storage_id' => $storageId,
                'number' => $startNumber + $offset,
                'code' => $code,
                'status' => 'available',
            ]);

            if ($offset === 0) {
                $firstUnit = $unit;
            }
        }

        Notification::make()
            ->title("Added {$quantity} unit(s) starting from " . str_pad((string) $startNumber, 4, '0', STR_PAD_LEFT) . '.')
            ->success()
            ->send();

        return $firstUnit;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('units', [
            'storage' => $this->getRecord()?->getAttribute('storage_id'),
            'item' => $this->getRecord()?->getAttribute('item_id'),
        ]);
    }
}
