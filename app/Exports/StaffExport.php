<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StaffExport implements FromCollection, WithHeadings, WithEvents
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
        $data = DB::table('staff as a')
            ->join('departments as b', 'b.id', '=', 'a.department_id')
            ->select('a.staff_id', 'a.staff_name', 'a.staff_email', 'a.staff_phoneno', 'b.dep_name as staff_department', 'a.staff_role', 'a.staff_status');

        if (!empty($this->selectedIds)) {
            $data->whereIn('a.id', $this->selectedIds);
        }

        $data = $data->get();

        if ($data->count() > 0) {
            // STATUS
            foreach ($data as $key => $value) {
                if ($value->staff_status == 1) {
                    $value->staff_status = 'Active';
                } elseif ($value->staff_status == 2) {
                    $value->staff_status = 'Inactive';
                } else {
                    $value->staff_status = 'N/A';
                }
            }
            foreach ($data as $key => $value) {
                if ($value->staff_role == 1) {
                    $value->staff_role = 'Committee';
                } elseif ($value->staff_role == 2) {
                    $value->staff_role = 'Lecturer';
                } elseif ($value->staff_role == 3) {
                    $value->staff_role = 'Timbalan Dekan Pendidikan';
                } elseif ($value->staff_role == 4) {
                    $value->staff_role = 'Dekan';
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Staff ID',
            'Name',
            'Email',
            'Phone Number',
            'Department',
            'Role',
            'Status'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $cellRange = 'A1:G1';

                // HEADER COLOUR
                $sheet->getStyle($cellRange)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                // MAKE TABLE BORDER
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:G$highestRow")->applyFromArray([
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

                // PHONE NUMBER COLUMN TO BE TEXT FORMAT
                $highestRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $cell = 'D' . $row;
                    $sheet->getCell($cell)->setValueExplicit(
                        $sheet->getCell($cell)->getValue(),
                        DataType::TYPE_STRING
                    );
                }
            },
        ];
    }
}
