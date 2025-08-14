<?php

namespace App\Exports\Support;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class PositionTypeSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected string $title;
    protected array $headings;
    protected array $rows;

    /** @var array<string,string> */
    protected array $labelOverrides;

    /**
     * @param string               $title          Naziv sheet-a
     * @param array                $headings       Poredak kolona (ključevи)
     * @param array                $rows           Redovi usklađeni sa $headings
     * @param array<string,string> $labelOverrides Override za heading label (npr. 'duzina' => 'Dužina (m)')
     */
    public function __construct(string $title, array $headings, array $rows, array $labelOverrides = [])
    {
        $this->title          = $title;
        $this->headings       = $headings;
        $this->rows           = $rows;
        $this->labelOverrides = $labelOverrides;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return array_map(function ($key) {
            // prvo proveri specifični override
            if (isset($this->labelOverrides[$key])) {
                return $this->labelOverrides[$key];
            }

            // inače humanizuj generički
            $label = str_replace('_', ' ', $key);
            $map = [
                'name'            => 'Naziv',
                'product name'    => 'Proizvod',
                'model'           => 'Model',
                'broj kom'        => 'Broj Komada',
                'duzina'          => 'Dužina',
                'sirina'          => 'Širina',
                'visina'          => 'Visina',
                'work order code' => 'Šifra Naloga',
            ];
            $norm = strtolower($label);
            if (isset($map[$norm])) {
                return $map[$norm];
            }
            $label = preg_replace('/\bid\b/i', 'ID', $label);
            return mb_convert_case($label, MB_CASE_TITLE, "UTF-8");
        }, $this->headings);
    }

    public function array(): array
    {
        return array_map(function ($row) {
            $out = [];
            foreach ($this->headings as $h) {
                $out[] = $row[$h] ?? null;
            }
            return $out;
        }, $this->rows);
    }

    public function styles(Worksheet $sheet)
    {
        $headerRow  = 1;
        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // Header stil
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '111827']], // gray-900
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'E5E7EB'], // gray-200
            ],
            'borders' => [
                'bottom' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D1D5DB']],
                'top'    => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D1D5DB']],
            ],
        ]);

        // Cela tabela (laki borduri, wrap, vertical-center)
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
            'alignment' => ['vertical' => 'center', 'wrapText' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => 'hair', 'color' => ['rgb' => 'E5E7EB']],
            ],
        ]);

        // Naglasi prve 3 kolone (Naziv, Proizvod, Model)
        foreach ([1, 2, 3] as $colIndex) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getFont()->setBold(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestCol = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // AutoFilter i freeze header
                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');

                // Zebra pruge
                for ($r = 2; $r <= $highestRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$highestCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'FAFAFA'],
                            ],
                        ]);
                    }
                }

                // Desno poravnanje numeričkih kolona po heuristici
                $headings = $this->headings;
                $colIndex = 1;
                foreach ($headings as $h) {
                    $isNumericHint = preg_match('/(sirina|visina|duzina|broj|povrsina|kom)/i', $h);
                    if ($isNumericHint) {
                        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getAlignment()->setHorizontal('right');
                    }
                    $colIndex++;
                }

                // Istakni poslednju kolonu (Šifra Naloga)
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol)
                );
                $sheet->getStyle("{$lastCol}1:{$lastCol}{$highestRow}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }
}
