<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class TypeShareSheet implements FromArray, WithTitle
{
    public function __construct(public array $data) {}

    public function array(): array
    {
        $rows = [['Tip naloga', 'Prihod (SUM total_price)']];
        foreach (($this->data['typeShare'] ?? []) as $type => $sum) {
            $rows[] = [$type, (float)$sum];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Prihod po tipu';
    }
}