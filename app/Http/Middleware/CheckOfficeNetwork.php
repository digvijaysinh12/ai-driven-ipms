<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class CheckOfficeNetwork
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowedIp($request->ip())) {
            return $next($request);
        }

        $message = 'This action is only allowed from the office WiFi network.';

        if ($request->expectsJson()) {
            abort(403, $message);
        }

        return back()->with('error', $message);
    }

    private function isAllowedIp(?string $ip): bool
    {
        if ($ip === null) {
            return false;
        }

        foreach (config('attendance.allowed_ips', []) as $allowedIp) {
            if ($allowedIp === '') {
                continue;
            }

            if (Str::contains($allowedIp, '*') && Str::is($allowedIp, $ip)) {
                return true;
            }

            if (! Str::contains($allowedIp, '*') && IpUtils::checkIp($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }
}
