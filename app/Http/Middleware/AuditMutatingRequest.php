<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use App\Services\AuditActionContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditMutatingRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly AuditActionContext $actionContext
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldAudit($request)) {
            return $next($request);
        }

        // Resolve and snapshot the target before the controller can update or delete it.
        // A context lookup must never prevent the underlying academic operation.
        try {
            $context = $this->actionContext->resolve($request);
        } catch (Throwable $e) {
            $context = [];
            Log::warning('Unable to resolve detailed audit subject context.', [
                'route' => $request->route()?->getName(),
                'request_id' => $request->attributes->get('request_id'),
                'exception' => $e,
            ]);
        }

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->write($request, 'failed', 500, $context);
            throw $e;
        }

        $hasApplicationError = $request->hasSession() && $request->session()->has('error');
        $outcome = !$hasApplicationError && ($response->isSuccessful() || $response->isRedirection())
            ? 'success'
            : 'failed';
        $this->write($request, $outcome, $response->getStatusCode(), $context);

        return $response;
    }

    private function shouldAudit(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        $auditableAuthRoute = $request->is('auth/*')
            && !in_array($routeName, ['user-authenticate', 'user-logout'], true);

        return !$request->isMethodSafe()
            && ($request->is('staff/*') || $request->is('student/*') || $request->is('signatures/*') || $auditableAuthRoute);
    }

    private function write(Request $request, string $outcome, int $status, array $context): void
    {
        $routeName = $request->route()?->getName() ?? $request->path();
        $category = $this->categoryFor($routeName);
        $resultMessage = $request->hasSession()
            ? ($request->session()->get('error') ?? $request->session()->get('success'))
            : null;
        $resultMessage = is_scalar($resultMessage) ? (string) $resultMessage : null;

        $description = $context['description'] ?? $this->descriptionFor($routeName, $outcome);
        if (isset($context['description'])) {
            $description = rtrim($description, '.') . ($outcome === 'success' ? ' completed successfully.' : ' failed.');
        }

        $contextMetadata = $context['metadata'] ?? [];
        $routeParameters = $contextMetadata['resolved_route_parameters']
            ?? ($request->route()?->parameters() ?? []);
        unset($contextMetadata['resolved_route_parameters']);

        $this->auditLogger->record(
            $category,
            $routeName,
            $description,
            [
                'outcome' => $outcome,
                'subject_type' => $context['subject_type'] ?? null,
                'subject_id' => $context['subject_id'] ?? null,
                'subject_label' => $context['subject_label'] ?? null,
                'metadata' => [
                    ...$contextMetadata,
                    'method' => $request->method(),
                    'route' => $routeName,
                    'route_parameters' => $routeParameters,
                    'request_data' => $this->auditLogger->sanitizedRequestData($request),
                    'response_status' => $status,
                    'result_message' => $resultMessage,
                ],
            ],
            $request
        );
    }

    private function categoryFor(string $routeName): string
    {
        return match (true) {
            str_contains($routeName, 'approval'), str_contains($routeName, 'approve'), str_contains($routeName, 'finalize') => 'approval',
            str_contains($routeName, 'signature') => 'signature',
            str_contains($routeName, 'delete'), str_contains($routeName, 'archive'), str_contains($routeName, 'remove') => 'deletion',
            str_contains($routeName, 'change-semester'), str_contains($routeName, 'status') => 'status',
            str_contains($routeName, 'faculty'), str_contains($routeName, 'department'), str_contains($routeName, 'programme-setting'),
            str_contains($routeName, 'semester-setting'), str_contains($routeName, 'procedure'), str_contains($routeName, 'activity-form'),
            str_contains($routeName, 'form-field'), str_contains($routeName, 'activity-post') => 'configuration',
            str_contains($routeName, 'add'), str_contains($routeName, 'import'), str_contains($routeName, 'enroll') => 'registration',
            str_contains($routeName, 'assign'), str_contains($routeName, 'supervision') => 'assignment',
            str_contains($routeName, 'setting') => 'configuration',
            default => 'update',
        };
    }

    private function descriptionFor(string $routeName, string $outcome): string
    {
        $action = str_replace(['-', '.'], ' ', $routeName);

        return ucfirst($action) . ($outcome === 'success' ? ' completed.' : ' failed.');
    }
}
