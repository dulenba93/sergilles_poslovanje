<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class StatusesSheet implements FromArray, WithTitle
{
    public function __construct(public array $data) {}

    public function array(): array
    {
        $labels = ['new'=>'Novi','in_progress'=>'U toku','done'=>'Završen','cancelled'=>'Otkazan'];
        $rows = [['Status', 'Broj']];
        foreach ($labels as $k => $label) {
            $rows[] = [$label, (int)($this->data['statusCounts'][$k] ?? 0)];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Statusi';
    }
}