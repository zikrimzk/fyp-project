<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;

class StaffExport implements FromCollection, WithEvents
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
            // STAFF STATUS
            foreach ($data as $key => $value) {
                if ($value->staff_status == 1) {
                    $value->staff_status = 'Active';
                } elseif ($value->staff_status == 2) {
                    $value->staff_status = 'Inactive';
                } else {
                    $value->staff_status = 'N/A';
                }
            }

            // STAFF ROLE
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

            // STAFF PHONE NUMBER
            foreach ($data as $key => $value) {
                if ($value->staff_phoneno == null) {
                    $value->staff_phoneno = '-';
                }
            }
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->insertNewRowBefore(1, 5);

                // TITLE
                $sheet->setCellValue('A2', 'E-POSTGRAD | UNIVERSITI TEKNIKAL MALAYSIA MELAKA (UTeM)');
                $sheet->setCellValue('A3', 'STAFF LIST');

                // STYLING HEADER AND CONTENT
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:G$highestRow")->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);

                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }


                // SET HEADER
                $headers = ['Staff ID', 'Name', 'Email', 'Phone Number', 'Department', 'Role', 'Status'];
                $column = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($column . '5', $header);
                    $column++;
                }

                // HEADER STYLING
                $sheet->getRowDimension(5)->setRowHeight(25);
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                // SET COLUMN WIDTH
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // SET BORDERS
                $sheet->getStyle("A5:G$highestRow")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
