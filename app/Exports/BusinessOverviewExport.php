<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusinessOverviewExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private array $data) {}

    public function array(): array
    {
        $d = $this->data;

        return [
            ['Pregled poslovanja', $d['from']->format('d.m.Y') . ' - ' . $d['to']->format('d.m.Y')],
            [],
            ['UPLATE (prilivi)'],
            ['RN – Ukupno',        $d['wo_paid_total']],
            ['RN – Keš',           $d['wo_paid_kes']],
            ['RN – Firma',         $d['wo_paid_firma']],
            ['Prodaje – Ukupno',   $d['sale_paid_total']],
            ['Prodaje – Keš',      $d['sale_paid_kes']],
            ['Prodaje – Firma',    $d['sale_paid_firma']],
            ['Uplate – Ukupno',    $d['in_total']],
            ['Uplate – Keš',       $d['in_kes']],
            ['Uplate – Firma',     $d['in_firma']],
            [],
            ['RASHODI (troškovi i dobavljači)'],
            ['Troškovi – Ukupno',  $d['exp_total']],
            ['Troškovi – Keš',     $d['exp_kes']],
            ['Troškovi – Firma',   $d['exp_firma']],
            ['Dobavljači – Ukupno',$d['vp_total']],
            ['Dobavljači – Keš',   $d['vp_kes']],
            ['Dobavljači – Firma', $d['vp_firma']],
            ['Rashodi – Ukupno',   $d['out_total']],
            ['Rashodi – Keš',      $d['out_kes']],
            ['Rashodi – Firma',    $d['out_firma']],
            [],
            ['FINALNI PRESEK'],
            ['Preostalo ukupno',   $d['net_total']],
            ['Preostalo Keš',      $d['net_kes']],
            ['Preostalo Firma',    $d['net_firma']],
        ];
    }

    public function title(): string
    {
        return 'Pregled poslovanja';
    }

    public function styles(Worksheet $sheet)
    {
        // Pronalaženje redova za bold/veći font:
        // 1: naslov, 3: "UPLATE", 15: "RASHODI", 25: "FINALNI PRESEK"
        $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A15')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A25')->getFont()->setBold(true)->setSize(12);

        // Ključni preseci podebljani i veći font
        // "Uplate – Ukupno" je u redu 12, "Rashodi – Ukupno" u redu 23, a finalni u 26-28.
        $sheet->getStyle('A12:B12')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A23:B23')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A26:B28')->getFont()->setBold(true)->setSize(12);

        // Lagani header stil u prvoj koloni
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:A{$highestRow}")->getFont()->getColor()->setRGB('374151'); // tamno siva

        return [];
    }
}