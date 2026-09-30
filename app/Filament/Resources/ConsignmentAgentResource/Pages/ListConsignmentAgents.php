<?php

namespace App\Filament\Resources\ConsignmentAgentResource\Pages;

use App\Filament\Resources\ConsignmentAgentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConsignmentAgents extends ListRecords
{
    protected static string $resource = ConsignmentAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}