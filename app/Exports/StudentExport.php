<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentExport implements FromCollection, WithEvents
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
        $data = DB::table('students as a')
            ->join('semesters as b', 'b.id', '=', 'a.semester_id')
            ->join('programmes as c', 'c.id', '=', 'a.programme_id')
            ->select('a.student_matricno', 'a.student_name', 'a.student_name', 'a.student_gender', 'a.student_email', 'a.student_phoneno', 'b.sem_label', 'c.prog_code', 'c.prog_mode', 'a.student_status');

        if (!empty($this->selectedIds)) {
            $data->whereIn('a.id', $this->selectedIds);
        }

        $data = $data->get();

        if ($data->count() > 0) {
            // STUDENT STATUS
            foreach ($data as $key => $value) {
                if ($value->student_status == 1) {
                    $value->student_status = 'Active';
                } elseif ($value->student_status == 2) {
                    $value->student_status = 'Inactive';
                } else {
                    $value->student_status = 'N/A';
                }
            }

            // STUDENT GENDER
            foreach ($data as $key => $value) {
                if ($value->student_gender == 'male') {
                    $value->student_gender = 'Male';
                } elseif ($value->student_gender == 'female') {
                    $value->student_gender = 'Female';
                } else {
                    $value->student_gender = 'N/A';
                }
            }

            // STUDENT PHONE NUMBER
            foreach ($data as $key => $value) {
                if ($value->student_phoneno == null) {
                    $value->student_phoneno = '-';
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
                $sheet->setCellValue('A3', 'STUDENT LIST');

                // STYLING HEADER AND CONTENT
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('A4:I4');
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:I$highestRow")->applyFromArray([
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
                $headers = ['Matric No', 'Student Name', 'Gender', 'Email', 'Phone Number', 'Semester', 'Programme', 'Mode', 'Status'];
                $column = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($column . '5', $header);
                    $column++;
                }

                // HEADER STYLING
                $sheet->getRowDimension(5)->setRowHeight(25);
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                // SET COLUMN WIDTH
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // SET BORDERS
                $sheet->getStyle("A5:I$highestRow")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // FORMAT : PHONE NUMBER
                for ($row = 5; $row <= $highestRow; $row++) {
                    $sheet->getCell('E' . $row)->setValueExplicit(
                        $sheet->getCell('E' . $row)->getValue(),
                        DataType::TYPE_STRING
                    );
                }
            },
        ];
    }
}
