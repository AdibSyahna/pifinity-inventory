<?php

namespace App\Filament\Resources\ConsignmentAgentResource\Pages;

use App\Filament\Resources\ConsignmentAgentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditConsignmentAgent extends EditRecord
{
    protected static string $resource = ConsignmentAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}