<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Postavi unit iz tipa ako nije ručno podešeno
        $data['unit'] = \App\Models\Sale::unitForType($data['type'] ?? null);
        return $data;
    }
}