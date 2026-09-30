<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItems;
use App\Filament\Resources\ItemResource\RelationManagers\UnitsRelationManager;
use App\Models\Item;
use App\Models\Tax;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Utilities\TaxService;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    private static function updateCalculations(Get $get, Set $set, int|float $tax): void
    {
        // Get current values
        $basePrice = (float) $get('price_item') ?? 0;
        $accPrice = (float) $get('price_acc') ?? 0;
        $shipPrice = (float) $get('price_ship') ?? 0;
        $minProfit = (float) $get('price_min_profit') ?? 0;
        $maxProfit = (float) $get('price_max_profit') ?? 0;

        $gross = $basePrice + $accPrice + $shipPrice;
        $finalMin = TaxService::calculateTax($gross, $tax, $minProfit, 5000);
        $finalMax = TaxService::calculateTax($gross, $tax, $maxProfit, 5000);

        // Set calculated values
        $set('price_round_min', $finalMin['round_up']);
        $set('price_tax_min', $finalMin['total_tax']);
        $set('total_price_min', $finalMin['total_price']);
        $set('total_profit_min', $finalMin['total_profit']);
        // _max
        $set('price_round_max', $finalMax['round_up']);
        $set('price_tax_max', $finalMax['total_tax']);
        $set('total_price_max', $finalMax['total_price']);
        $set('total_profit_max', $finalMax['total_profit']);
    }

    public static function form(Schema $schema): Schema
    {
        $tax = (int) Tax::firstOrFail()->percentage;

        return $schema
            ->components([
                Section::make('Item Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label("Category")
                            ->required()
                            ->searchable()
                            ->relationship('category', 'name')
                            ->preload(),
                        Select::make('size_id')
                            ->label("Size")
                            ->relationship('size', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('color_id')
                            ->label("Color")
                            ->relationship('color', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        RichEditor::make('description')
                            ->columnSpanFull(),
                        TextInput::make('weight')
                            ->numeric()
                            ->default(0)
                            ->suffix('g')
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('default')
                            ->conversion('thumb')
                            ->multiple()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Prices')
                    ->schema([
                        TextInput::make('price_item')
                            ->label('Base Price')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp.'),
                        TextInput::make('price_acc')
                            ->label('Accessories Price')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp.'),
                        TextInput::make('price_ship')
                            ->label('Shipping Price')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp.'),
                        TextInput::make('price_min_profit')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                            ->label('Minimum Profit')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp.'),
                        TextInput::make('price_max_profit')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                            ->label('Target Profit')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp.')
                    ])->columns(2),
                Section::make('Calculations')
                    ->schema([
                        Section::make("On Minimum Price")
                            ->schema([
                                TextInput::make('price_tax_min')
                                    ->default(0)
                                    ->live()
                                    ->label('Tax')
                                    ->suffix('(' . $tax . '%)')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('price_round_min')
                                    ->default(0)
                                    ->live()
                                    ->label('Rounding')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('total_profit_min')
                                    ->default(0)
                                    ->live()
                                    ->label('Total profit')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('total_price_min')
                                    ->default(0)
                                    ->live()
                                    ->afterStateHydrated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                                    ->label('Total price')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.')
                            ])->columns(2),
                        Section::make("On Target Price")
                            ->schema([
                                TextInput::make('price_tax_max')
                                    ->default(0)
                                    ->live()
                                    ->label('Tax')
                                    ->suffix('(' . $tax . '%)')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('price_round_max')
                                    ->default(0)
                                    ->live()
                                    ->label('Rounding')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('total_profit_max')
                                    ->default(0)
                                    ->live()
                                    ->label('Total profit')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.'),
                                TextInput::make('total_price_max')
                                    ->default(0)
                                    ->live()
                                    ->afterStateHydrated(fn(Get $get, Set $set) => ItemResource::updateCalculations($get, $set, $tax))
                                    ->label('Total price')
                                    ->readOnly()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('Rp.')
                            ])->columns(2),
                    ]),
                Section::make('Tags')
                    ->schema([
                        SpatieTagsInput::make('tags')
                            ->type('default')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('thumb')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('size.name')
                    ->label('Size')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('color.name')
                    ->label('Color')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('weight')
                    ->suffix(' g')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->badge()
                    ->color(fn(string $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('stocks_count')
                    ->counts('units')
                    ->label('Stocks')
                    ->sortable()
                    ->toggleable(),
                SpatieTagsColumn::make('tags'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('is_active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
                ForceDeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItems::route('/'),
            'create' => CreateItem::route('/create'),
            'edit' => EditItem::route('/{record}/edit'),
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
