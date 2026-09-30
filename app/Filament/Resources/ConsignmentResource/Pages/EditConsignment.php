<?php

namespace App\Filament\Resources\ConsignmentResource\Pages;

use App\Filament\Resources\ConsignmentResource;
use App\Models\ConsignmentAgent;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditConsignment extends EditRecord
{
    protected static string $resource = ConsignmentResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->mountAction('confirm_error_items');

        if ($this->record->status == 'draft' && !$this->checkIfAllItemsAreAvailable()) {
            Notification::make()
                ->danger()
                ->title('One or more items has become unavailable')
                ->persistent()
                ->body('All items must be available before the consignment contract can be activated.')
                ->send();
        }
    }

    private function checkIfAllItemsAreAvailable(): bool
    {
        $this->record->load('units.unit');
        $allAvailable = $this->record->units->every(
            fn($unit) => $unit->unit->status == 'available'
        );
        return $allAvailable;
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Activate')
                ->disabled(function () {
                    if ($this->record->status != 'draft') {
                        return true;
                    };
                    if (!$this->checkIfAllItemsAreAvailable()) {
                        return true;
                    }
                })
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalIconColor('info')
                ->modalIcon('heroicon-o-question-mark-circle')
                ->modalHeading('Activate consignment contract?')
                ->modalDescription('Make sure the items are available or has been picked up by the Consignment Agent.')
                ->action(function () {
                    DB::beginTransaction();
                    try {
                        $this->record->load('units.unit');

                        $today = Carbon::now(); // or Carbon::today() if you only want the date, no time
                        $this->record->update([
                            'status' => 'active',
                            'start_date' => $today,
                        ]);

                        $allAvailable = $this->record->units->every(
                            fn($unit) => $unit->unit->status == 'available'
                        );
                        if (! $allAvailable) {
                            throw new \Exception('Not all units are available.');
                        }

                        $this->record->units->each(function ($unit) {
                            $unit->update(['status' => 'active']);
                            $unit->unit->update(['status' => 'consignment']);
                        });

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->danger()
                            ->title('One or more items has become unavailable')
                            ->persistent()
                            ->body('All items must be available before the consignment contract can be activated.')
                            ->send();
                        throw $e;
                    }
                }),

            DeleteAction::make()
        ];
    }

    public function getTitle(): string
    {
        $agent = ConsignmentAgent::query()->findOrFail($this->record->agent_id);

        return 'Consignment of ' . $agent->name . ' (' . $this->record->agreement_number . ')';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'active' && is_null($this->getRecord()?->getAttribute('start_date'))) {
            $data['start_date'] = date('Y-m-d');
        }

        return $data;
    }
}
