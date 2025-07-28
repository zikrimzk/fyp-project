<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Submission;
use Illuminate\Support\Str;
use App\Mail\SubmissionMail;
use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendSubmissionReminders extends Command
{
    protected $signature = 'submission:reminder';
    protected $description = 'Send submission reminders 1 month, 1 week, and 1 day before due date';

    public function handle()
    {
        $now = Carbon::now()->startOfDay();

        $targetDates = [
            $now->copy()->addMonth()->toDateString(),
            $now->copy()->addWeek()->toDateString(),
            $now->copy()->addDay()->toDateString(),
        ];

        $submissions = DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->join('students as d', 'a.student_id', '=', 'd.id')
            ->where('a.submission_status', 1)
            ->whereIn(DB::raw("DATE(a.submission_duedate)"), $targetDates)
            ->select(
                'a.submission_duedate',
                'b.doc_name',
                'b.activity_id',
                'c.act_name',
                'a.student_id',
                'd.student_name',
                'd.student_matricno',
                'd.student_email'
            )
            ->get();

        $grouped = $submissions->groupBy(function ($item) {
            return $item->student_id . '-' . $item->activity_id;
        });

        foreach ($grouped as $group) {
            $first = $group->first();

            $documents = $group->map(function ($doc) {
                return (object)[
                    'doc_name' => $doc->doc_name,
                    'submission_duedate' => $doc->submission_duedate
                ];
            });

            $data = [
                'student_name' => $first->student_name,
                'student_matricno' => $first->student_matricno,
                'student_email' => $first->student_email,
                'document' => $documents,
                'submission_date' => null,
            ];

            $this->sendSubmissionNotification((object)$data, 1, $first->act_name, 1, null);
            $this->info("Reminder sent to {$first->student_email} for {$first->act_name}");
        }

        return 0;
    }


    private function sendSubmissionNotification($data, $userType, $actName, $emailType, $approvalRole)
    {
        if ($userType == 1) {
            $name = $data->student_name;
            $email = $data->student_email;
        } else {
            $name = null;
            $email = null;
        }

        if (env('MAIL_ENABLE') == 'true') {
            Mail::to($email)->send(new SubmissionMail([
                'eType' => $emailType,
                'act_name' => $actName,
                'approvalUser' => null,
                'name' => Str::headline($name),
                'sa_date' => Carbon::now()->format('d F Y g:i A'),
                'student_name' => '-',
                'student_matricno' => '-',
                'submission_date' => '-',
                'document' => $data->document,
            ]));
        }
    }
}
