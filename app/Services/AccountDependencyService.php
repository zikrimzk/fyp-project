<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AccountDependencyService
{
    private const STUDENT_REFERENCES = [
        'supervisions' => 'supervision assignments',
        'submissions' => 'submission records',
        'student_activities' => 'confirmed activities',
        'student_semesters' => 'semester enrollments',
        'nominations' => 'nomination records',
        'evaluations' => 'evaluation records',
        'activity_corrections' => 'correction records',
        'journal_publications' => 'journal publications',
    ];

    private const STAFF_REFERENCES = [
        'supervisions' => 'supervision assignments',
        'submission_reviews' => 'submission reviews',
        'evaluators' => 'evaluator assignments',
        'evaluations' => 'evaluation records',
    ];

    public function forStudent(int $studentId): array
    {
        return $this->counts(self::STUDENT_REFERENCES, 'student_id', $studentId);
    }

    public function forStaff(int $staffId): array
    {
        return $this->counts(self::STAFF_REFERENCES, 'staff_id', $staffId);
    }

    public function studentReferences(): \Illuminate\Database\Query\Builder
    {
        return $this->referenceQuery(array_keys(self::STUDENT_REFERENCES), 'student_id');
    }

    public function staffReferences(): \Illuminate\Database\Query\Builder
    {
        return $this->referenceQuery(array_keys(self::STAFF_REFERENCES), 'staff_id');
    }

    public function hasReferences(array $dependencies): bool
    {
        return array_sum(array_column($dependencies, 'count')) > 0;
    }

    public function summary(array $dependencies): string
    {
        $parts = [];
        foreach ($dependencies as $dependency) {
            if ($dependency['count'] > 0) {
                $parts[] = $dependency['count'] . ' ' . $dependency['label'];
            }
        }

        return implode(', ', $parts);
    }

    private function counts(array $references, string $column, int $id): array
    {
        $counts = [];
        foreach ($references as $table => $label) {
            $counts[$table] = [
                'label' => $label,
                'count' => DB::table($table)->where($column, $id)->count(),
            ];
        }

        return $counts;
    }

    private function referenceQuery(array $tables, string $column): \Illuminate\Database\Query\Builder
    {
        $query = DB::table(array_shift($tables))->select($column);
        foreach ($tables as $table) {
            $query->union(DB::table($table)->select($column));
        }

        return $query;
    }
}
