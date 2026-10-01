<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruTemplate implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return collect([
            ['Ahmad Fauzi', 'ahmad@guru.com', '081234567890', 'Guru PAI', ''],
            ['Siti Aminah', 'siti@guru.com', '081234567891', 'Guru Tahfidz', ''],
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'Email', 'Telepon', 'Jabatan', 'Password'];
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
