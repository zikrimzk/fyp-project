<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSubmissionStatus extends Command
{
    protected $signature = 'submission:update-status';
    protected $description = 'Automatically update submission status to 4 (overdue) or 1 (pending) based on due date';

    public function handle()
    {
        $now = Carbon::now();

        // Update to overdue (status = 4)
        $overdue = DB::table('submissions')
            ->whereIn('submission_status', [1, 4])
            ->whereDate('submission_duedate', '<', $now->toDateString())
            ->update(['submission_status' => 4]);

        // Update back to pending (status = 1) if due date is still valid
        $pending = DB::table('submissions')
            ->whereIn('submission_status', [1, 4])
            ->whereDate('submission_duedate', '>=', $now->toDateString())
            ->update(['submission_status' => 1]);

        $this->info("Updated $overdue submissions to overdue (4).");
        $this->info("Updated $pending submissions to pending (1).");

        return 0;
    }
}
