<?php

namespace App\Imports;

use App\Http\Controllers\AuthenticateController;
use App\Models\Department;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StaffImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */

    public $insertedCount = 0;
    public $skippedCount = 0;
    public $skippedRows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            if ($row->filter()->isEmpty()) {
                continue;
            }

            //Staff Role
            if (Str::upper($row['staff_role']) == 'COMM') {
                $row['staff_role'] = 1;
            } elseif (Str::upper($row['staff_role']) == 'LECT') {
                $row['staff_role'] = 2;
            } elseif (Str::upper($row['staff_role']) == 'TDP') {
                $row['staff_role'] = 3;
            } elseif (Str::upper($row['staff_role']) == 'DEAN') {
                $row['staff_role'] = 4;
            }

            //Department
            if (Str::upper($row['staff_department']) != null) {
                $department = Department::where('dep_code', Str::upper($row['staff_department']))->first();
                $row['staff_department'] = $department->id ?? null;
            }

            $trimmedData = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $row->toArray());

            $validator = Validator::make($trimmedData, [
                'staff_name' => 'required|string|max:255',
                'staff_id' => 'required|string|unique:staff,staff_id',
                'staff_email' => 'required|email|unique:staff,staff_email',
                'staff_password' => 'nullable|string|min:8|max:50',
                'staff_phoneno' => 'nullable|max:20',
                'staff_role' => 'required|integer|in:1,2,3,4',
                'staff_department' => 'required|integer|exists:departments,id',
            ], [], [
                'staff_name' => 'staff name',
                'staff_id' => 'staff ID',
                'staff_email' => 'staff email',
                'staff_password' => 'password',
                'staff_phoneno' => 'staff phone number',
                'staff_role' => 'staff role',
                'staff_department' => 'department',
            ]);

            if ($validator->fails()) {
                $this->skippedCount++;
                $this->skippedRows[] = [
                    'data' => $row->toArray(),
                    'errors' => $validator->errors()->all()
                ];
                continue;
            }

            $validated = $validator->validated();

            /* SET STAFF INITIAL PASSWORD */
            $password = bcrypt("pg@" . Str::lower($validated['staff_id']));

            /* CREATE STAFF DATA */
            $staff = Staff::create([
                'staff_name' => Str::headline($validated['staff_name']),
                'staff_id' => Str::upper($validated['staff_id']),
                'staff_email' => $validated['staff_email'],
                'staff_password' => $password,
                'staff_phoneno' => $validated['staff_phoneno'] ?? null,
                'staff_role' => $validated['staff_role'],
                'department_id' => $validated['staff_department'],
            ]);

            /* SENT EMAIL NOTIFICATION - Function | Last Added: 06-08-2026 */
            $ac = new AuthenticateController();
            $ac->sendAccountNotification($staff, 1, 2, route('main-login'));

            $this->insertedCount++;
        }
    }
}
