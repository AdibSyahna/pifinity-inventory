<?php

namespace App\Filament\Resources\ItemUnitsResource\Pages;

use App\Filament\Resources\ItemUnitsResource;
use App\Models\Consignment;
use App\Models\ConsignmentUnits;
use App\Models\Item;
use App\Models\ItemUnits;
use App\Models\Storage;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class ListUnitsForItem extends ListRecords
{
    protected static string $resource = ItemUnitsResource::class;

    protected static ?string $title = 'Units';

    #[Url(as: 'storage')]
    public int $storage;

    #[Url(as: 'item')]
    public int $item;

    private ?Item $itemModel = null;

    private ?Storage $storageModel = null;

    protected function getItemModel(): Item
    {
        return $this->itemModel ??= Item::query()->findOrFail($this->item);
    }

    protected function getStorageModel(): Storage
    {
        return $this->storageModel ??= Storage::query()->findOrFail($this->storage);
    }

    public function getTitle(): string
    {
        return $this->getItemModel()->name . ' in ' . $this->getStorageModel()->name . ' storage';
    }

    protected function getTableQuery(): Builder
    {
        return ItemUnits::query()
            ->where('storage_id', $this->storage)
            ->where('item_id', $this->item);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('number')
                    ->label('Number')
                    ->formatStateUsing(fn(string $state): string => str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'consignment' => 'info',
                        'sold' => 'warning',
                        'missing' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
                // make filter based on `status`
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'consignment' => 'Consignment',
                        'missing' => 'Missing',
                    ])
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('consignment')
                    ->label('Add to consignment')
                    ->icon('heroicon-o-document-plus')
                    ->disabled(function ($livewire) {
                        $status = $livewire->getTableFilterState('status')['value'] ?? null;
                        return $status !== 'available';
                    })
                    ->tooltip(function ($livewire) {
                        if (($livewire->getTableFilterState('status')['value'] ?? null) !== 'available') {
                            return 'Enable filter by "Available" status to enable this action.';
                        }
                        return null;
                    })
                    ->schema([
                        Select::make('consignment_id')
                            ->label('Consignment')
                            ->options(function (): array {
                                return Consignment::query()
                                    ->with('agent')
                                    ->where('status', 'draft')
                                    ->get()
                                    ->mapWithKeys(fn(Consignment $consignment): array => [
                                        $consignment->id => $consignment->agreement_number
                                            . ' | '
                                            . ($consignment?->agent?->name ?? 'No agent'),
                                    ])
                                    ->toArray();
                            })
                            ->required(),
                        // Section::make('Prices')
                        //     ->schema([
                        //         TextInput::make('price_original')
                        //             ->label('Original Price')
                        //             ->default($this->getItemModel()->priceFinal())
                        //             ->readOnly()
                        //             ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                        //             ->prefix('Rp.'),
                        //         TextInput::make('profit_original')
                        //             ->label('Original Profit')
                        //             ->default($this->getItemModel()->profitFinal())
                        //             ->readOnly()
                        //             ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                        //             ->prefix('Rp.'),
                        //         TextInput::make('commission')
                        //             ->label('Commission per sale')
                        //             ->default(0)
                        //             ->required()
                        //             ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                        //             ->prefix('Rp.'),

                        //     ])
                        //     ->columns(3)
                    ])
                    ->action(function (Collection $records, array $data) {
                        DB::beginTransaction();

                        try {
                            foreach ($records as $record) {
                                // loop $data
                                if ($record['status'] != 'available') {
                                    throw new \Exception('One or more selected item is not in "Available" status.');
                                }

                                // check for duplicate in ConsignmentUnit
                                // using unit_id and consignment_id
                                $consignmentUnit = ConsignmentUnits::where([
                                    'unit_id' => $record['id'],
                                    'consignment_id' => $data['consignment_id'],
                                ])->first();
                                if ($consignmentUnit) {
                                    continue; // silent skip
                                }

                                ConsignmentUnits::create([
                                    'consignment_id' => $data['consignment_id'],
                                    'unit_id' => $record['id'],
                                    'storage_id' => $this->storage,
                                    'price_original' => $this->getItemModel()->priceFinal(),
                                    'profit_original' => $this->getItemModel()->profitFinal(),
                                    'commission' => $data['commission'],
                                ]);
                                continue;
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    })
                    ->successNotificationTitle('Units added to consignment.')
                    ->deselectRecordsAfterCompletion()
            ]);
    }
}
