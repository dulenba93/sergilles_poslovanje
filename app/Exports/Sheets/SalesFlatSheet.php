<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesFlatSheet implements FromArray, WithTitle
{
    public function __construct(public Collection $sales) {}

    public function array(): array
    {
        $rows = [[
            'Šifra', 'Tip', 'Artikal', 'JM', 'Količina', 'Cena/JM', 'Ukupno', 'Plaćeno', 'Plaćanje', 'Kupac/Opis', 'Datum'
        ]];

        foreach ($this->sales as $s) {
            $rows[] = [
                $s->code,
                $s->type,
                $s->product?->name,
                $s->unit,
                (float)$s->quantity,
                (float)($s->unit_price ?? 0),
                (float)$s->total_price,
                (float)$s->paid_amount,
                $s->payment_type,
                $s->customer_description,
                optional($s->created_at)->format('d.m.Y H:i'),
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Sve prodaje';
    }
}