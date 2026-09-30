<?php

namespace App\Filament\Resources\ItemUnitsResource\Pages;

use App\Filament\Resources\ItemUnitsResource;
use App\Models\Storage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListUnits extends ListRecords
{
    protected static string $resource = ItemUnitsResource::class;

    protected static ?string $title = 'Units';

    protected function getTableQuery(): Builder
    {
        return Storage::query()
            ->whereHas('units');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Storage')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Units')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([])
            ->groupedBulkActions([])
            ->recordUrl(fn (Model $record, Table $table): ?string => $this->getResourceUrl('storage', ['storage' => $record->getKey()]));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}