<?php

namespace App\Filament\Resources\ItemUnitsResource\Pages;

use App\Filament\Resources\ItemUnitsResource;
use App\Models\Item;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

class ListUnitsInStorage extends ListRecords
{
    protected static string $resource = ItemUnitsResource::class;

    protected static ?string $title = 'Items in Storage';

    #[Url(as: 'storage')]
    public int $storage;

    protected function getTableQuery(): Builder
    {
        return Item::query()
            ->whereHas('units', fn (Builder $query): Builder => $query->where('storage_id', $this->storage));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('size.name')
                    ->label('Size')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([])
            ->groupedBulkActions([])
            ->recordUrl(fn (Model $record, Table $table): ?string => $this->getResourceUrl('units', [
                'storage' => $this->storage,
                'item' => $record->getKey(),
            ]));
    }
}