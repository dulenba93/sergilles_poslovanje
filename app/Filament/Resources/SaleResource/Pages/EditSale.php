<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // osveži unit prema tipu
        $data['unit'] = \App\Models\Sale::unitForType($data['type'] ?? null);
        return $data;
    }
}