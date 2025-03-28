<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SupervisionExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds ? explode(',', $selectedIds) : null;
    }
    public function collection()
    {
        $rawData = DB::table('supervisions as a')
            ->join('students as b', 'b.id', '=', 'a.student_id')
            ->join('staff as c', 'c.id', '=', 'a.staff_id')
            ->join('programmes as d', 'd.id', '=', 'b.programme_id')
            ->whereIn('a.student_id', $this->selectedIds)
            ->select('b.student_name', 'b.student_matricno', 'd.prog_code', 'd.prog_mode', 'c.staff_name', 'a.supervision_role')
            ->get();

        $formattedData = [];
        foreach ($rawData as $item) {
            $matricNo = $item->student_matricno;

            if (!isset($formattedData[$matricNo])) {
                $formattedData[$matricNo] = [
                    'student_name' => $item->student_name,
                    'student_matricno' => $item->student_matricno,
                    'prog_name' => $item->prog_code,
                    'prog_mode' => $item->prog_mode,
                    'main_supervisor' => null,
                    'co_supervisor' => null
                ];
            }

            if ($item->supervision_role == 1) {
                $formattedData[$matricNo]['main_supervisor'] = $item->staff_name;
            } elseif ($item->supervision_role == 2) {
                $formattedData[$matricNo]['co_supervisor'] = $item->staff_name;
            }
        }

        $exportData = collect(array_values($formattedData));
        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Matric No',
            'Programme',
            'Mode',
            'Main Supervisor',
            'Co-Supervisor',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $cellRange = 'A1:F1';

                // HEADER COLOUR
                $sheet->getStyle($cellRange)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                // MAKE TABLE BORDER
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:F$highestRow")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'size' => 12,
                    ],
                ]);

                // SET ROW HEIGHT
                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // SET FONT SIZE
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'size' => 12,
                        'bold' => true
                    ],
                ]);

                // SET COLUMN WIDTH
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

            },
        ];
    }
}
