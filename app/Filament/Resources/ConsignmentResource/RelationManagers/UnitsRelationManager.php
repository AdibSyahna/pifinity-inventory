<?php

namespace App\Filament\Resources\ConsignmentResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('unit.code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('unit.item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('storage.name')
                    ->label('Storage')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_original')
                    ->label('Original Price')
                    ->money('Rp.')
                    ->sortable(),
                TextColumn::make('commission')
                    ->label('Commission')
                    ->money('Rp.')
                    ->sortable(),
                TextColumn::make('final_price')
                    ->getStateUsing(fn($record) => $record->price_original - $record->commission)
                    ->label('Final Price')
                    ->money('Rp.')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'warning',
                        'active' => 'secondary',
                        'sold' => 'success',
                        'returned' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->sortable(),
                TextColumn::make('unit.status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'consignment' => 'info',
                        'sold' => 'warning',
                        'missing' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->sortable()
            ])
            ->checkIfRecordIsSelectableUsing(fn($record) => $record->status == 'draft')
            ->filters([
                SelectFilter::make('unit_status')
                    ->label('Unit Status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'consignment' => 'Consignment',
                        'missing' => 'Missing',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, string $value): Builder => $query->whereHas(
                                'unit',
                                fn(Builder $query): Builder => $query->where('status', $value),
                            ),
                        );
                    })
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn($record) => $record->status == 'draft'),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
