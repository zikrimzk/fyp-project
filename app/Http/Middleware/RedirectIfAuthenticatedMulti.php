<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticatedMulti
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student-home');
        }

        if (Auth::guard('staff')->check()) {
            return redirect()->route('staff-dashboard');
        }

        return $next($request);
    }
}
