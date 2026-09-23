<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProcedureFlowBuilder
{
    public function grouped(?int $programmeId = null): Collection
    {
        return DB::table('procedures as p')
            ->join('activities as a', 'a.id', '=', 'p.activity_id')
            ->join('programmes as pr', 'pr.id', '=', 'p.programme_id')
            ->when($programmeId, fn ($query) => $query->where('p.programme_id', $programmeId))
            ->select([
                'p.activity_id',
                'p.programme_id',
                'p.activity_type',
                'p.act_seq',
                'p.timeline_sem',
                'p.timeline_week',
                'p.init_status',
                'p.is_haveEva',
                'p.evaluation_mode',
                'p.is_repeatable',
                'p.is_haveJournalPublication',
                'p.material',
                'a.act_name',
                'pr.prog_code',
                'pr.prog_name',
                'pr.prog_mode',
            ])
            ->orderBy('pr.prog_code')
            ->orderBy('pr.prog_mode')
            ->orderBy('p.act_seq')
            ->orderBy('p.activity_id')
            ->get()
            ->groupBy('programme_id')
            ->map(fn ($procedures) => $procedures->values());
    }

    public function forProgramme(int $programmeId): Collection
    {
        return $this->grouped($programmeId)->get($programmeId, collect());
    }
}
