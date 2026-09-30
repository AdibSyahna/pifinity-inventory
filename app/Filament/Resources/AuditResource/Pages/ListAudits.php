<?php

namespace App\Filament\Resources\AuditResource\Pages;

use App\Filament\Resources\AuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('prune')
                ->label('Prune Old Audits')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function (): void {
                    \Illuminate\Support\Facades\Artisan::call('audits:prune', ['--days' => 365]);
                    \Filament\Notifications\Notification::make()
                        ->title('Audit logs older than 1 year have been pruned.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Prune Audit Logs')
                ->modalDescription('This will permanently delete all audit logs older than 1 year. This action cannot be undone.'),
        ];
    }
}