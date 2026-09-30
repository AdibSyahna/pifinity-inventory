<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditResource\Pages\ListAudits;
use App\Filament\Resources\AuditResource\Pages\ViewAudit;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Models\Audit;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $slug = 'audit-logs';

    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Information')
                    ->schema([
                        TextInput::make('event')
                            ->disabled(),
                        TextInput::make('auditable_type')
                            ->label('Auditable Type')
                            ->disabled(),
                        TextInput::make('auditable_id')
                            ->label('Auditable ID')
                            ->disabled(),
                        TextInput::make('user_type')
                            ->label('User Type')
                            ->disabled(),
                        TextInput::make('user_id')
                            ->label('User ID')
                            ->disabled(),
                        TextInput::make('url')
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->disabled(),
                        TextInput::make('user_agent')
                            ->disabled(),
                        TextInput::make('tags')
                            ->disabled(),
                        DateTimePicker::make('created_at')
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Old Values')
                    ->schema([
                        KeyValue::make('old_values')
                            ->disabled(),
                    ]),
                Section::make('New Values')
                    ->schema([
                        KeyValue::make('new_values')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('auditable_type')
                    ->label('Auditable Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('auditable_id')
                    ->label('Auditable ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Audited At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ]),
                SelectFilter::make('auditable_type')
                    ->label('Auditable Type')
                    ->options(fn (): array => Audit::query()
                        ->distinct()
                        ->pluck('auditable_type')
                        ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                        ->toArray()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Information')
                    ->schema([
                        TextEntry::make('id'),
                        TextEntry::make('event')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                'restored' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('auditable_type')
                            ->formatStateUsing(fn (string $state): string => class_basename($state)),
                        TextEntry::make('auditable_id'),
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('url')
                            ->copyable(),
                        TextEntry::make('ip_address'),
                        TextEntry::make('user_agent')
                            ->copyable(),
                        TextEntry::make('tags'),
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A'),
                    ])
                    ->columns(3),
                Section::make('Changes')
                    ->schema([
                        KeyValueEntry::make('old_values')
                            ->label('Old Values'),
                        KeyValueEntry::make('new_values')
                            ->label('New Values'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
            'view' => ViewAudit::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Audit::query()->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['auditable_type', 'event', 'user.name'];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}