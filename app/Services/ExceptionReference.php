<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ExceptionReference
{
    public function report(Throwable $exception, ?Request $request = null): string
    {
        $request ??= request();
        $reference = $request?->attributes->get('request_id') ?: (string) Str::uuid();

        Log::error('Application operation failed.', [
            'reference_id' => $reference,
            'route' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'actor_staff_id' => $request?->user('staff')?->id,
            'actor_student_id' => $request?->user('student')?->id,
            'exception' => $exception,
        ]);

        app(AuditLogger::class)->record('exception', 'application-exception', 'An application operation failed.', [
            'request_id' => $reference,
            'outcome' => 'failed',
            'metadata' => [
                'exception_type' => $exception::class,
                'route' => $request?->route()?->getName(),
            ],
        ], $request);

        return $reference;
    }

    public function friendlyMessage(Throwable $exception, string $action = 'complete this action', ?Request $request = null): string
    {
        $reference = $this->report($exception, $request);

        return "We couldn't {$action}. Please try again. If the problem continues, contact the system administrator and provide reference ID {$reference}.";
    }
}
