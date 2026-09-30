<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsignmentResource\Pages\CreateConsignment;
use App\Filament\Resources\ConsignmentResource\Pages\EditConsignment;
use App\Filament\Resources\ConsignmentResource\Pages\ListConsignments;
use App\Filament\Resources\ConsignmentResource\RelationManagers\UnitsRelationManager;
use App\Models\Consignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsignmentResource extends Resource
{
    protected static ?string $model = Consignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Consignment';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Consignment';

    protected static ?string $pluralModelLabel = 'Consignments';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Consignment Information')
                    ->schema([
                        Select::make('agent_id')
                            ->label('Agent')
                            ->relationship('agent', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                            ])
                            ->default('draft')
                            ->disabled()
                            ->required(),
                        TextInput::make('duration_days')
                            ->label("Duration")
                            ->helperText("Consignment duration in days")
                            ->numeric()
                            ->minValue(1)
                            ->suffix('days')
                            ->required(),
                        Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agreement_number')
                    ->label('Agreement No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->state(fn(Consignment $record): string => $record->effectiveStatus())
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'overdue' => 'warning',
                        'finished' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords($state)),
                TextColumn::make('start_date')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(),
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
            'index' => ListConsignments::route('/'),
            'create' => CreateConsignment::route('/create'),
            'edit' => EditConsignment::route('/{record}/edit'),
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
