<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemUnitsResource\Pages\CreateUnits;
use App\Filament\Resources\ItemUnitsResource\Pages\ListUnits;
use App\Filament\Resources\ItemUnitsResource\Pages\ListUnitsForItem;
use App\Filament\Resources\ItemUnitsResource\Pages\ListUnitsInStorage;
use App\Models\ItemUnits;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemUnitsResource extends Resource
{
    protected static ?string $model = ItemUnits::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';

    protected static string | \UnitEnum | null $navigationGroup = "Inventory";

    protected static ?string $slug = 'units';

    protected static ?string $modelLabel = 'Unit';

    protected static ?string $pluralModelLabel = 'Units';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Add Units')
                    ->schema([
                        Select::make('storage_id')
                            ->relationship('storage', 'name')
                            ->label('Storage')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('item_id')
                            ->relationship('item', 'name')
                            ->label('Item')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('The number of units to generate.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Number')
                    ->formatStateUsing(fn (string $state): string => str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'sold' => 'warning',
                        'consignment' => 'info',
                        'missing' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'storage' => ListUnitsInStorage::route('/storage/{storage}'),
            'units' => ListUnitsForItem::route('/storage/{storage}/item/{item}'),
            'create' => CreateUnits::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}