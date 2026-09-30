<?php

namespace App\Filament\Resources\ConsignmentResource\Pages;

use App\Filament\Resources\ConsignmentResource;
use App\Models\Consignment;
use Filament\Resources\Pages\CreateRecord;

class CreateConsignment extends CreateRecord
{
    protected static string $resource = ConsignmentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['agreement_number'] ?? null)) {
            $data['agreement_number'] = Consignment::nextAgreementNumber();
        }

        if (($data['status'] ?? null) === 'active' && blank($data['start_date'] ?? null)) {
            $data['start_date'] = date('Y-m-d');
        }

        return $data;
    }
}
