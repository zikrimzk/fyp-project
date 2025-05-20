<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;

class MySupervisionStudentExport implements FromCollection, WithEvents
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

        $latestSemesterSub = DB::table('student_semesters')
            ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
            ->groupBy('student_id');

        $rawData = DB::table('supervisions as a')
            ->join('students as b', 'b.id', '=', 'a.student_id')
            ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                $join->on('latest.student_id', '=', 'b.id');
            })
            ->leftJoin('student_semesters as ss', function ($join) {
                $join->on('ss.student_id', '=', 'b.id')
                    ->on('ss.semester_id', '=', 'latest.latest_semester_id');
            })
            ->leftJoin('semesters as sem', 'sem.id', '=', 'ss.semester_id')
            ->join('staff as c', 'c.id', '=', 'a.staff_id')
            ->join('programmes as d', 'd.id', '=', 'b.programme_id')
            ->select(
                'b.student_matricno',
                'b.student_name',
                'd.prog_code',
                'd.prog_mode',
                'c.staff_name',
                'a.supervision_role',
            )
            ->where('a.staff_id', auth()->user()->id)
            ->orderBy('b.student_name');

        if (!empty($this->selectedIds)) {
            $rawData->whereIn('a.student_id', $this->selectedIds);
        }

        $rawData = $rawData->get();

        $formattedData = [];
        foreach ($rawData as $item) {
            $matricNo = $item->student_matricno;

            if (!isset($formattedData[$matricNo])) {
                $formattedData[$matricNo] = [
                    'student_matricno' => $item->student_matricno,
                    'student_name' => $item->student_name,
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->insertNewRowBefore(1, 5);

                // TITLE
                $sheet->setCellValue('A2', 'E-POSTGRAD | UNIVERSITI TEKNIKAL MALAYSIA MELAKA (UTeM)');
                $sheet->setCellValue('A3', 'MY SUPERVISION STUDENT LIST');

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

                $sheet->getStyle("A1:F$highestRow")->applyFromArray([
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
                $headers = ['Matric No', 'Student Name', 'Programme', 'Mode', 'Main Supervisor', 'Co-Supervisor'];
                $column = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($column . '5', $header);
                    $column++;
                }

                // HEADER STYLING
                $sheet->getRowDimension(5)->setRowHeight(25);
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                // SET COLUMN WIDTH
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // SET BORDERS
                $sheet->getStyle("A5:F$highestRow")->applyFromArray([
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
