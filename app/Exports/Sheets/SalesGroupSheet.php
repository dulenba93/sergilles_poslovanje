<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesGroupSheet implements FromArray, WithTitle
{
    public function __construct(public string $groupName, public Collection $items) {}

    public function array(): array
    {
        $rows = [[
            'Šifra', 'Tip', 'JM', 'Količina', 'Cena/JM', 'Ukupno', 'Plaćeno', 'Plaćanje', 'Kupac/Opis', 'Datum'
        ]];

        foreach ($this->items as $s) {
            $rows[] = [
                $s->code,
                $s->type,
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
        // Excel sheet title limit 31 chars
        return mb_strimwidth($this->groupName ?: 'Grupa', 0, 31, '…', 'UTF-8');
    }
}