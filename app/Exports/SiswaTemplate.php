<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaTemplate implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return collect([
            ['Ahmad Fauzi', 'L', '7A', '0012345678', '1001', 'ortu_ahmad@email.com', ''],
            ['Siti Aminah', 'P', '7B', '', '', '', ''],
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'Gender (L/P)', 'Nama Kelas', 'NISN', 'NIS', 'Email Orangtua', 'RFID'];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => '059669']]],
        ];
    }
}
