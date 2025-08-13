<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SummarySheet implements FromArray, WithTitle
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public array $data
    ) {}

    public function array(): array
    {
        return [
            ['Period', $this->from->toDateString() . ' - ' . $this->to->toDateString()],
            ['Broj porudžbina', $this->data['totalOrders']],
            ['Ukupan očekivan', $this->data['expected']],
            ['Ukupno naplaćeno', $this->data['paid']],
            ['Preostalo', $this->data['remaining']],
            ['Montaže', $this->data['montaze']],
        ];
    }

    public function title(): string
    {
        return 'Sažetak';
    }
}
