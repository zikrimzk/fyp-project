<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $categories = [
            'authentication', 'email', 'approval', 'signature', 'registration',
            'assignment', 'status', 'update', 'deletion', 'configuration', 'exception',
        ];

        $validated = $request->validate([
            'category' => ['nullable', Rule::in($categories)],
            'outcome' => ['nullable', Rule::in(['success', 'failed', 'skipped'])],
            'actor_type' => ['nullable', Rule::in(['staff', 'student', 'system'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $logs = AuditLog::query()
            ->when($validated['category'] ?? null, fn ($query, $value) => $query->where('category', $value))
            ->when($validated['outcome'] ?? null, fn ($query, $value) => $query->where('outcome', $value))
            ->when($validated['actor_type'] ?? null, fn ($query, $value) => $query->where('actor_type', $value))
            ->when($validated['date_from'] ?? null, fn ($query, $value) => $query->whereDate('occurred_at', '>=', $value))
            ->when($validated['date_to'] ?? null, fn ($query, $value) => $query->whereDate('occurred_at', '<=', $value))
            ->when($validated['search'] ?? null, function ($query, $value) {
                $query->where(function ($inner) use ($value) {
                    $term = '%' . addcslashes($value, '%_\\') . '%';
                    $inner->where('event', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('actor_name', 'like', $term)
                        ->orWhere('actor_identifier', 'like', $term)
                        ->orWhere('subject_label', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhere('request_id', 'like', $term);
                });
            })
            ->latest('occurred_at')
            ->paginate(50)
            ->withQueryString();

        return view('staff.audit.index', [
            'title' => 'Audit Log',
            'logs' => $logs,
            'categories' => $categories,
        ]);
    }
}
