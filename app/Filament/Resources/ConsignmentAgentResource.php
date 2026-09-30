<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsignmentAgentResource\Pages\CreateConsignmentAgent;
use App\Filament\Resources\ConsignmentAgentResource\Pages\EditConsignmentAgent;
use App\Filament\Resources\ConsignmentAgentResource\Pages\ListConsignmentAgents;
use App\Models\ConsignmentAgent;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsignmentAgentResource extends Resource
{
    protected static ?string $model = ConsignmentAgent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Consignment';

    protected static string|null $navigationLabel = 'Agents';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agent Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->required()
                            ->maxLength(30)
                            ->tel()
                            ->placeholder('+62...'),
                        TextInput::make('email')
                            ->email()
                            ->placeholder('agent@example.com'),
                        Textarea::make('address')
                            ->rows(3),
                        Select::make('id_type')
                            ->label('ID Type')
                            ->placeholder('Select an ID type')
                            ->options([
                                'passport' => 'Passport',
                                'national_id' => 'National ID',
                                'driver_license' => 'Driver License',
                            ]),
                        TextInput::make('id_number')
                            ->label('ID Number'),
                    ])
                    ->columns(2),
                Section::make('Photos')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('id_photo')
                            ->collection('id_photo')
                            ->image()
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('portrait')
                            ->collection('portrait')
                            ->image()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('id_photo')
                    ->collection('id_photo')
                    ->conversion('thumb')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                SpatieMediaLibraryImageColumn::make('portrait')
                    ->collection('portrait')
                    ->conversion('thumb')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label('Phone Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('id_type')
                    ->label('ID Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'passport' => 'Passport',
                        'national_id' => 'National ID',
                        'driver_license' => 'Driver License',
                        default => $state,
                    })
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsignmentAgents::route('/'),
            'create' => CreateConsignmentAgent::route('/create'),
            'edit' => EditConsignmentAgent::route('/{record}/edit'),
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
