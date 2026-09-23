<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Trust only explicitly configured reverse proxies so forwarded client IPs
     * cannot be spoofed by a direct request.
     */
    protected function proxies()
    {
        $configured = config('app.trusted_proxies');

        return is_string($configured) && trim($configured) !== ''
            ? array_map('trim', explode(',', $configured))
            : null;
    }
}
