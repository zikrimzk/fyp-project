<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password', 'oldPass', 'renewPass',
        'renewPass_confirmation', '_token', 'signature_data', 'signature', 'token',
    ];

    public function record(
        string $category,
        string $event,
        string $description,
        array $context = [],
        ?Request $request = null,
        ?Authenticatable $actor = null
    ): ?AuditLog {
        try {
            $request ??= request();
            $actor ??= $this->resolveActor($request);
            $actorType = $context['actor_type'] ?? $this->actorType($actor);
            $metadata = $this->sanitize($context['metadata'] ?? []);

            if ($actor) {
                $metadata['actor'] = array_filter([
                    'type' => $actorType,
                    'database_id' => $actor->getKey(),
                    'identifier' => $this->actorIdentifier($actor),
                    'name' => $this->actorName($actor),
                    'role' => $this->actorRole($actor),
                ], fn ($value) => $value !== null && $value !== '');
            }

            if ($request) {
                $metadata['client'] = $this->clientContext($request);
            }

            return AuditLog::create([
                'request_id' => $context['request_id'] ?? $request?->attributes->get('request_id'),
                'occurred_at' => now(),
                'category' => Str::limit($category, 40, ''),
                'event' => Str::limit($event, 120, ''),
                'outcome' => $context['outcome'] ?? 'success',
                'actor_type' => $actorType,
                // Staff/Student override the authentication identifier with email,
                // while audit relations need the stable numeric database key.
                'actor_id' => $context['actor_id'] ?? $actor?->getKey(),
                'actor_identifier' => $context['actor_identifier'] ?? $this->actorIdentifier($actor),
                'actor_name' => $context['actor_name'] ?? $this->actorName($actor),
                'subject_type' => $context['subject_type'] ?? null,
                'subject_id' => isset($context['subject_id']) ? (string) $context['subject_id'] : null,
                'subject_label' => $context['subject_label'] ?? null,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            ]);
        } catch (Throwable $e) {
            // Audit failures must be visible to operations without breaking the academic workflow.
            Log::critical('Unable to write audit log.', [
                'event' => $event,
                'exception' => $e,
                'request_id' => $request?->attributes->get('request_id'),
            ]);

            return null;
        }
    }

    public function sanitizedRequestData(Request $request): array
    {
        return $this->sanitize($request->except(self::SENSITIVE_KEYS));
    }

    private function sanitize(array $data): array
    {
        if (count($data) > 200) {
            $originalCount = count($data);
            $data = array_slice($data, 0, 200, true);
            $data['_audit_truncated'] = $originalCount - 200 . ' additional values omitted';
        }

        $sensitiveKeys = array_map('strtolower', self::SENSITIVE_KEYS);

        foreach ($data as $key => &$value) {
            $normalizedKey = strtolower((string) $key);
            $isSensitive = in_array($normalizedKey, $sensitiveKeys, true)
                || str_contains($normalizedKey, 'password')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'signature');

            if ($isSensitive) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $value = $this->sanitize($value);
            } elseif (is_string($value)) {
                $value = Str::limit($value, 1000, '...');
            }
        }
        unset($value);

        return $data;
    }

    private function resolveActor(?Request $request): ?Authenticatable
    {
        return $request?->user('staff') ?? $request?->user('student') ?? $request?->user();
    }

    private function actorType(?Authenticatable $actor): ?string
    {
        if (!$actor) {
            return null;
        }

        return property_exists($actor, 'staff_id') || isset($actor->staff_id) ? 'staff' : 'student';
    }

    private function actorIdentifier(?Authenticatable $actor): ?string
    {
        return $actor?->staff_id ?? $actor?->student_matricno ?? $actor?->getAuthIdentifier();
    }

    private function actorName(?Authenticatable $actor): ?string
    {
        return $actor?->staff_name ?? $actor?->student_name ?? null;
    }

    private function actorRole(?Authenticatable $actor): ?string
    {
        if (!$actor) {
            return null;
        }

        if (isset($actor->staff_role)) {
            return [1 => 'Committee', 2 => 'Lecturer', 3 => 'Deputy Dean', 4 => 'Dean'][(int) $actor->staff_role] ?? 'Staff';
        }

        return isset($actor->student_matricno) ? 'Student' : null;
    }

    private function clientContext(Request $request): array
    {
        $userAgent = (string) $request->userAgent();

        return array_filter([
            'ip_address' => $request->ip(),
            // request()->ips() only honors forwarding headers from configured trusted proxies.
            'ip_chain' => $request->ips(),
            'browser' => $this->browser($userAgent),
            'operating_system' => $this->operatingSystem($userAgent),
            'device_type' => $this->deviceType($userAgent),
            'language' => Str::limit((string) $request->header('Accept-Language'), 120, ''),
            'session_reference' => $request->hasSession() && $request->session()->getId()
                ? substr(hash('sha256', $request->session()->getId()), 0, 20)
                : null,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function browser(string $userAgent): string
    {
        $patterns = [
            'Microsoft Edge' => '/Edg(?:A|iOS)?\/([\d.]+)/',
            'Opera' => '/(?:OPR|Opera)\/([\d.]+)/',
            'Firefox' => '/(?:Firefox|FxiOS)\/([\d.]+)/',
            'Google Chrome' => '/(?:Chrome|CriOS)\/([\d.]+)/',
            'Safari' => '/Version\/([\d.]+).*Safari/',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return $name . ' ' . $matches[1];
            }
        }

        return $userAgent === '' ? 'Unknown' : 'Other / Unknown';
    }

    private function operatingSystem(string $userAgent): string
    {
        return match (true) {
            preg_match('/Windows NT 10\.0/i', $userAgent) === 1 => 'Windows 10/11',
            preg_match('/Windows NT 6\.3/i', $userAgent) === 1 => 'Windows 8.1',
            preg_match('/Android\s+([\d.]+)/i', $userAgent, $matches) === 1 => 'Android ' . $matches[1],
            preg_match('/(?:iPhone|CPU) OS ([\d_]+)/i', $userAgent, $matches) === 1 => 'iOS ' . str_replace('_', '.', $matches[1]),
            preg_match('/Mac OS X ([\d_]+)/i', $userAgent, $matches) === 1 => 'macOS ' . str_replace('_', '.', $matches[1]),
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Other / Unknown',
        };
    }

    private function deviceType(string $userAgent): string
    {
        return match (true) {
            preg_match('/bot|crawler|spider|slurp/i', $userAgent) === 1 => 'Bot',
            preg_match('/iPad|Tablet|PlayBook/i', $userAgent) === 1 => 'Tablet',
            preg_match('/Mobile|iPhone|Android/i', $userAgent) === 1 => 'Mobile',
            default => 'Desktop',
        };
    }
}
