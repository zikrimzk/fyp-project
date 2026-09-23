<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NominationNavigation
{
    public function tabsFor(Staff $staff, string $routeName): Collection
    {
        $query = DB::table('procedures as p')
            ->join('activities as a', 'a.id', '=', 'p.activity_id')
            ->where('p.is_haveEva', 1)
            ->select('a.id', 'a.act_name')
            ->distinct()
            ->orderBy('a.act_name');

        if ($routeName === 'nomination-approval') {
            $signatureRole = [3 => 5, 4 => 6][(int) $staff->staff_role] ?? null;
            if ($signatureRole) {
                $query->whereExists(function ($subquery) use ($signatureRole) {
                    $subquery->selectRaw('1')
                        ->from('activity_forms as af')
                        ->join('form_fields as ff', 'ff.af_id', '=', 'af.id')
                        ->whereColumn('af.activity_id', 'a.id')
                        ->where('af.af_target', 3)
                        ->where('ff.ff_category', 6)
                        ->where('ff.ff_signature_role', $signatureRole);
                });
            }
        }

        $counts = app(StaffWorkCounts::class)->forStaff($staff);

        return $query->get()->map(function ($activity) use ($routeName, $counts) {
            $slug = Str::slug($activity->act_name);
            $url = route($routeName, $slug);

            return (object) [
                'id' => (int) $activity->id,
                'name' => $activity->act_name,
                'slug' => $slug,
                'url' => $url,
                'pending_count' => (int) ($counts[$url] ?? 0),
            ];
        });
    }
}
